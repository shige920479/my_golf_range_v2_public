<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Controller\User\Traits\HandleFormValidateTrait;
use App\Database\DbConnect;
use App\Exceptions\NotFoundException;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\External\OpenWeatherCache;
use App\Services\External\OpenWeatherClient;
use App\Services\External\OpenWeatherService;
use App\Services\Security\TokenManager;
use App\Services\User\CalcFeeService;
use App\Services\User\ReserveService;
use App\Services\Validation\Reserve\FacilityValidation;
use App\Services\Validation\Reserve\FormValidation;
use App\Services\Validation\Reserve\ReservableValidation;
use App\Services\Validation\Reserve\TimeValidation;
use Carbon\Carbon;

class UpdateReservationController extends BaseController
{
  private ReserveService $reserveService;
  private FormValidation $formValidator;
  private FacilityValidation $facilityValidator;
  private TimeValidation $timeValidator;
  private ReservableValidation $reservableValidator;
  private CalcFeeService $calcFeeService;
  private OpenWeatherService $weatherService;
  use HandleFormValidateTrait;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    ReserveService $reserveService,
    FormValidation $formValidator,
    FacilityValidation $facilityValidator,
    TimeValidation $timeValidator,
    ReservableValidation $reservableValidator,
    CalcFeeService $calcFeeService,
  )
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard); 
    $this->reserveService = $reserveService;
    $this->formValidator = $formValidator;
    $this->facilityValidator = $facilityValidator;
    $this->timeValidator = $timeValidator;
    $this->reservableValidator = $reservableValidator;
    $this->calcFeeService = $calcFeeService;
    $this->authGuard->checkAuth('user');

    $client = new OpenWeatherClient();
    $cache = new OpenWeatherCache();
    $this->weatherService = new OpenWeatherService($client, $cache);
  }

  /** 予約変更画面表示 */
  public function edit(int $id)
  {
    $this->reserveService->checkOwner($id);
    $reservation = $this->reserveService->getReservationById($id);
    if($reservation === null) {
      throw new NotFoundException("予約番号 {$id}: 予約データが存在しません");
    }
    $reserveDate = $this->request['search_date'] ?? $reservation['reserve_date'] ?? Carbon::today()->format('Y-m-d');

    $userId = $_SESSION['user']['id'];
    $data = $this->reserveService->getReservePageData($reserveDate, $userId);
    $data['reservation'] = $reservation;
    if(! empty($_SESSION['reservation'])) $data['temporary'] = $this->clearSession();
    $data['weather'] = $this->weatherService->getWeatherByDate($reserveDate);
    $data['title'] = '予約内容の変更';

    $this->render(APP_PATH . '/Views/user/edit-reserve.php', $data);
  }

  /** 予約変更 */
  public function update(int $id)
  {
    $this->reserveService->checkOwner($id);
    $this->validateReservationForm($this->request, "/reservation/{$id}/edit");

    $fees = $this->calcFeeService->calcFee($this->request);
    $reservation = $this->reserveService->createReservation($this->request, $fees);
    $this->session->set('reservation', $reservation);

    redirect(url("/reservation/{$id}/confirm"));
  }

  /** 予約内容確認 */
  public function updateConfirm(int $id)
  {
    $this->reserveService->checkOwner($id);
    $reservation = $this->session->get('reservation');
    $otherData = $this->reserveService->getOtherDisplayData($reservation);

    $this->render(APP_PATH . '/Views/user/edit-confirm.php', [
      'reservation' => $reservation,
      'otherData' => $otherData,
      'id' => $id,
      'title' => '変更内容確認'
    ]);
  }

  /** 予約変更 */
  public function updateStore(int $id)
  {
    $this->reserveService->checkOwner($id);
    $reservation = $this->session->get('reservation');
    
    $this->reserveService->updateReservation($id, $reservation);

    $this->session->forget('reservation');
    redirect(url('/reservation/updateComplete'));
  }
  
  /** 完了画面 */
  public function updateComplete()
  {
    $this->render(APP_PATH . '/Views/user/edit-complete.php', ['title' => '変更登録完了']);
  }

  /** 予約キャンセル */
  public function delete(int $id)
  {
    $this->reserveService->checkOwner($id);
    $this->reserveService->cancelReservation($id);

    redirect(url('/mypage'));
  }

  /** セッションに保存した変更情報をクリア */ 
  private function clearSession(): array
  {
    $old = $this->session->get('reservation');
    $this->session->forget('reservation');

    return $old;
  }
}