<?php
namespace App\Services\User;

use App\Database\DbConnect;
use App\Exceptions\ForbiddenException;
use App\Exceptions\ServerErrorException;
use App\Repositories\FeeRepository;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ReserveRangeRepository;
use App\Repositories\ReserveRentalRepository;
use App\Repositories\ReserveShowerRepository;
use App\Repositories\ShowerRepository;
use Carbon\Carbon;
use Exception;

class ReserveService
{
  public function __construct(
    private RangeRepository $rangeRepo,
    private RentalRepository $rentalRepo,
    private ShowerRepository $showerRepo,
    private ReserveRangeRepository $reserveRangeRepo,
    private ReserveRentalRepository $reserveRentalRepo,
    private ReserveShowerRepository $reserveShowerRepo,
    private ReserveFormService $formService,
    private ReserveTableService $tableService,
    private FeeRepository $feeRepo,
    private DbConnect $db,
  )
  {
  }

  public function getReservePageData(string $date, int $userId): array
  {
    $rangeRows = $this->reserveRangeRepo->getRangeReservation($date);
    $optionRows = $this->reserveRentalRepo->unionRentalAndShowerReservation($date);
    $timeSlots = $this->formService->generateTime();

    return [
      // 予約入力情報
      'form'            => $this->formService->getFormData(),
      'reservableDates' => $this->formService->availableDate(),
      'startTimes'      => $timeSlots,
      'endTimes'        => $this->formService->generateTime(30),
      'rentals'         => $this->rentalRepo->get(),
      'searchDate'      => $date,
      
      // タイムテーブル系
      'calenderTimes'   => $this->formService->generateTime(0, 60),
      'rangeMatrix'     => $this->tableService->getMatrix($timeSlots, $rangeRows, $date, $userId),
      'optionMatrix'    => $this->tableService->getMatrix($timeSlots, $optionRows, $date, $userId),
      'backUrl'         => normalizeUri($_SERVER['REQUEST_URI'], PHP_URL_PATH),

    ];
  }

  public function createReservation(array $request, array $fees): array
  {
    $data = array_merge($request, $fees);
    $keys = [
      'range_id', 'number', 'reserve_date', 'start_time', 'end_time', 'entrance_fee', 'range_hourly_fee',
      'rental', 'rental_id', 'rental_fee',
      'shower', 'shower_time', 'shower_fee', 'usage_time', 'back_url'];

    $reservation = [];
    foreach($keys as $key) {
      $reservation[$key] = $data[$key] ?? '';
    }

    return $reservation;
  }

  public function getOtherDisplayData(array $reservation): array|false
  {
    $rangeData = $this->rangeRepo->getById($reservation['range_id']);
    if ($reservation['rental_id'] !== '') {
      $rentalData = $this->rentalRepo->getById($reservation['rental_id']);
    }
    return [
      'format_date' => Carbon::parse($reservation['reserve_date'])->isoFormat('MM月DD日(ddd)'),
      'range_name' => $rangeData['name'],
      'brand' => $rentalData['brand'] ?? '',
      'model' => $rentalData['model'] ?? ''
    ];
  }

  public function storeReservation(int $userId, array $reservation): void
  {
    $reservation['user_id'] = $userId;
    $reservation['range_fee'] = $reservation['entrance_fee'] + $reservation['range_hourly_fee'];
    if($reservation['shower'] !== '') {
      $reservation['shower_end'] = Carbon::createFromTimeString($reservation['shower_time'])
                                    ->addMinutes(30)->format('H:i:s');
    }

    try {
      $this->db->beginTransaction();
  
      $this->reserveRangeRepo->store($reservation);
      $reservation['reserveRange_id'] = $this->db->lastInsertId();

      if($reservation['rental'] !== '') $this->reserveRentalRepo->store($reservation);

      if($reservation['shower'] !== '') $this->reserveShowerRepo->store($reservation);
  
      $this->db->commit();

    } catch(Exception $e) {

      $this->db->rollBack();
      throw new ServerErrorException("予約登録時にシステムエラーが発生しました");
    }
  }

  /**
   * 予約idから予約データ取得
   * (データ無ければnull)
   */
  public function getReservationById(int $id): ?array
  {
    return $this->reserveRangeRepo->getById($id) ?: null;
  }

  /** 予約変更 */
  public function updateReservation(int $id, array $reservation): void
  {
    $reservation['range_fee'] = $reservation['entrance_fee'] + $reservation['range_hourly_fee'];
    if($reservation['shower'] !== '') {
      $reservation['shower_end'] = Carbon::createFromTimeString($reservation['shower_time'])
                                    ->addMinutes(30)->format('H:i:s');
    }

    try {
      $this->db->beginTransaction();
  
      $this->reserveRangeRepo->update($id, $reservation);

      $isReserved = $this->reserveRentalRepo->exists($id);
      if($reservation['rental'] !== '' && $isReserved) {
        $this->reserveRentalRepo->update($id, $reservation);

      } elseif($reservation['rental'] !== '' && ! $isReserved) {
        $reservation['user_id'] = $_SESSION['user']['id'];
        $reservation['reserveRange_id'] = $id;
        $this->reserveRentalRepo->store($reservation);

      } elseif($reservation['rental'] === '' && $isReserved) {
        $this->reserveRentalRepo->cancel($id);
      }

      $isReserved = $this->reserveShowerRepo->exists($id);
      if($reservation['shower'] !== '' && $isReserved) {
        $this->reserveShowerRepo->update($id, $reservation);

      } elseif($reservation['shower'] !== '' && ! $isReserved) {
        $reservation['user_id'] = $_SESSION['user']['id'];
        $reservation['reserveRange_id'] = $id;
        $this->reserveShowerRepo->store($reservation);

      } elseif($reservation['shower'] === '' && $isReserved) {
        $this->reserveShowerRepo->cancel($id);
      }
  
      $this->db->commit();

    } catch(Exception $e) {

      $this->db->rollBack();
      throw new ServerErrorException("予約登録時にシステムエラーが発生しました");
    }
  }

  /** 予約キャンセル */
  public function cancelReservation(int $id): void // policy
  {
    try {
      $this->reserveRangeRepo->cancel($id);
      $this->reserveRentalRepo->cancel($id);
      $this->reserveShowerRepo->cancel($id);

    } catch(Exception $e) {
      $this->db->rollBack();
      throw new ServerErrorException("キャンセル処理に失敗: 予約番号:{$id}");
    }
  }

  public function checkOwner(int $id): void
  {
    $userId = $_SESSION['user']['id'] ?? null;
    if(! $this->reserveRangeRepo->isOwnedByUser($id, $userId)) {
      throw new ForbiddenException("アクセス権限がありません。予約id：{$id} / ユーザーid：{$userId}");
    }
  }

}