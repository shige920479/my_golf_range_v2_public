<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Database\DbConnect;
use App\Exceptions\BadRequestException;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Owner\SettingService;
use App\Services\Security\TokenManager;
use App\Services\Validation\Facility\SettingValidation;
use Carbon\Carbon;

class SettingController extends BaseController
{
  private SettingService $settingService;
  private SettingValidation $validator;
  private const RANGE_FEE = 'rangeFee';
  private const RENTAL_FEE = 'rentalFee';
  private const SHOWER_FEE = 'showerFee';
  private string $currentUri;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    SettingService $settingService,
    SettingValidation $validator
  )
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler,  $db, $authGuard);
    $this->settingService = $settingService;
    $this->validator = $validator;
    $this->currentUri = normalizeUri(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
  }

  public function index()
  {
    $today = Carbon::today()->format('Y-m-d');
    $data = $this->settingService->buildSettingData($today);

    $this->render(APP_PATH . '/Views/owner/index.php', $data);
  }

  /** 料金変更設定　ドライビングレンジ */
  public function createRangeFee()
  {
    $fee = $this->settingService->getFee(self::RANGE_FEE);
    if($fee['feature'] !== null) {
      $this->session->set('errors.range', '既に改定予定があるので登録できません');
      redirect(url('/owner/index'));
    }

    $this->render(APP_PATH . '/Views/owner/create-fee.php', [
      'currentUri' => $this->currentUri,
      'fee' => $fee['current']
    ]);
  }

  /** 料金変更設定　オプション */
  public function createOptionFee(string $table)
  {
    $fee = $this->settingService->getFee($table);
    if($fee['feature'] !== null) {
      $this->session->set('errors.' . $table, '既に改定予定があるので登録できません');
      redirect(url('/owner/index'));
    }
    $this->render(APP_PATH . '/Views/owner/create-fee.php', [
      'currentUri' => $this->currentUri,
      'fee' => $fee['current']
    ]);
  }

  /** 料金変更　ドライビングレンジ */
  public function storeRangeFee()
  {
    if (! $this->validator->rangeFeeValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->validator);
      redirect(url('/owner/create/rangeFee'));
    }
    $this->settingService->store($this->request, self::RANGE_FEE);
    $this->session->set('success.range', 'ドライビングレンジの改定料金を登録しました');
    redirect(url('owner/index'));
  }
  /** 料金変更設定　オプション */
  public function storeOptionFee(string $table)
  {
    if ($table === self::RENTAL_FEE) {
      if (! $this->validator->rentalFeeValidation($this->request)) {
        $this->setSessionErrorsAndOld($this->validator);
        redirect(url('/owner/create/option/' . $table));
      }
    }
    if ($table === self::SHOWER_FEE) {
      if (! $this->validator->showerFeeValidation($this->request)) {
        $this->setSessionErrorsAndOld($this->validator);
        redirect(url('/owner/create/option/' . $table));
      }
    }
    if($table !== self::RENTAL_FEE && $table !== self::SHOWER_FEE) {
      throw new BadRequestException("{$table}へのアクセスは存在しません、不正なリクエストです。");
    }

    $this->settingService->store($this->request, $table);

    $section = $table === self::RENTAL_FEE ? 'レンタルクラブ' : 'シャワー利用';
    $this->session->set('success.' . $table, "{$section}の改定料金を登録しました");
    redirect(url('owner/index'));
  }

  public function editMainte(string $table, int $id)
  {
    $editData = $this->settingService->getMainteData($table, $id);

    $this->render(APP_PATH . '/Views/owner/create-fee.php', [
      'currentUri' => substr($this->currentUri, 0, 13),
      'table' => $table,
      'id' => $id,
      'data' => $editData,
    ]);
  }

  public function updateMainte(string $table, int $id)
  {
    if(! $this->validator->mainteDateValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->validator);
      redirect(url("/owner/mainte/{$table}/{$id}"));
    }

    $this->settingService->updateMainteDate($table, $id, $this->request['mainte_date']);
    $this->session->set('success.mainte_date', 'メンテナンス日を新規設定しました');

    redirect(url('owner/index'));
  }
}