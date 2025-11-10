<?php
namespace App\Controller\Owner;

use App\Controller\BaseController;
use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Owner\InitRegisterService;
use App\Services\Security\TokenManager;
use App\Services\Validation\Facility\InitValidation;
use App\Services\Validation\Facility\SettingValidation;

class InitRegisterController extends BaseController
{
  private InitValidation $validator;
  private SettingValidation $settingValidator;
  private InitRegisterService $initRegisterService;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    InitValidation $validator,
    SettingValidation $settingValidator,
    InitRegisterService $initRegisterService,
  )
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard);
    $this->validator = $validator;
    $this->settingValidator = $settingValidator;
    $this->initRegisterService = $initRegisterService;
  }

  public function create()
  {
    $this->render(APP_PATH . '/Views/owner/init-registration.php', [
      'section' => $this->session->flash('section'),
      'effectiveDateOld' => $this->session->flash('old.effective_date'),
      'effectiveDateError' => $this->session->flash('errors.effective_date')
    ]);
  }

  public function storeRange()
  {
    if (! $this->validator->rangeValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->validator);
      redirect(url('/owner/initial'));
    }

    $this->initRegisterService->storeRangeService($this->request);
    $this->session->set('success.drivingRange', 'ドライビングレンジの初期登録が完了しました');

    redirect(url('/owner/initial'));
  }

  public function storeRangeFee()
  {
    if (! $this->settingValidator->rangeFeeValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->settingValidator);
      $this->session->set('section', 'rangeFee');
      redirect(url('/owner/initial'));
    }

    $this->initRegisterService->storeRangeFeeService($this->request);
    $this->session->set('success.rangeFee', 'レンジ料金の初期登録が完了しました');

    redirect(url('/owner/initial'));
  }
  
  public function storeRental()
  {
    if (! $this->validator->rentalValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->validator);
      redirect(url('/owner/initial'));
    }

    $this->initRegisterService->storeRentalService($this->request);
    $this->session->set('success.rental', 'レンタルクラブの初期登録が完了しました');

    redirect(url('/owner/initial'));
  }

  public function storeRentalFee()
  {
    if (! $this->settingValidator->rentalFeeValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->settingValidator);
      $this->session->set('section', 'rentalFee');
      redirect(url('/owner/initial'));
    }

    $this->initRegisterService->storeRentalFeeService($this->request);
    $this->session->set('success.rentalFee', 'レンタルクラブ料金の初期登録が完了しました');

    redirect(url('/owner/initial'));    
  }

  public function storeShowerFee()
  {
    if (! $this->settingValidator->showerFeeValidation($this->request)) {
      $this->setSessionErrorsAndOld($this->settingValidator);
      $this->session->set('section', 'showerFee');
      redirect(url('/owner/initial'));
    }

    $this->initRegisterService->storeShowerFeeService($this->request);
    $this->session->set('success.showerFee', 'シャワー料金の初期登録が完了しました');

    redirect(url('/owner/initial'));   
    
  }

}