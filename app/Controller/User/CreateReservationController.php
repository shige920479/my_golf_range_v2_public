<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Controller\User\Traits\HandleFormValidateTrait;
use App\Database\DbConnect;
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

class CreateReservationController extends BaseController
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

  /** 予約画面表示 */
  public function index()
  {
    $searchDate = $this->request['search_date'] ?? Carbon::today()->format('Y-m-d');
    $userId = $_SESSION['user']['id'];
    $data = $this->reserveService->getReservePageData($searchDate, $userId);
    if(! empty($_SESSION['reservation'])) $data['temporary'] = $this->clearSession();
    $data['weather'] = $this->weatherService->getWeatherByDate($searchDate);
    $data['title'] = '新規予約登録';

    $this->render(APP_PATH . '/Views/user/reserve.php', $data);
  }

  /** 予約登録 */
  public function send()
  {
    $this->validateReservationForm($this->request, '/reservation');

    $fees = $this->calcFeeService->calcFee($this->request);
    $reservation = $this->reserveService->createReservation($this->request, $fees);
    $this->session->set('reservation', $reservation);

    redirect(url('/reservation/confirm'));
  }

  /** 予約内容確認 */
  public function confirm()
  {
    $reservation = $this->session->get('reservation');
    $otherData = $this->reserveService->getOtherDisplayData($reservation);

    $this->render(APP_PATH . '/Views/user/reserve-confirm.php', [
      'reservation' => $reservation,
      'otherData' => $otherData,
      'title' => '予約内容確認'
    ]);
  }
  
  /** 予約登録 */
  public function store()
  {
    $userId = $_SESSION['user']['id'];
    $reservation = $this->session->get('reservation');
    
    $this->reserveService->storeReservation($userId, $reservation);

    $this->session->forget('reservation');
    redirect(url('/reservation/complete'));
  }

  /** 完了画面 */
  public function complete()
  {
    $this->render(APP_PATH . '/Views/user/reserve-complete.php', ['title' => '予約登録完了']);
  }

  /** セッションに保存した変更情報をクリア */ 
  private function clearSession(): array
  {
    $old = $this->session->get('reservation');
    $this->session->forget('reservation');

    return $old;
  }
}