<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Repositories\TemporaryUserRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\UserRegisterService;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;
use App\Services\Validation\UserRegisterValidaion;

class RegisterController extends BaseController
{
  private string $mode;
  private UserRegisterValidaion $validator;
  private UserRegisterService $registerService;
  private TemporaryUserRepository $tempRepo;
  private UserRepository $userRepo;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    UserRegisterValidaion $validator,
    UserRegisterService $registerService,
    TemporaryUserRepository $tempRepo,
    UserRepository $userRepo
  )
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard);

    $this->validator = $validator;
    $this->registerService = $registerService;
    $this->tempRepo = $tempRepo;
    $this->userRepo = $userRepo;

    $url = parse_url($_SERVER['REQUEST_URI']);
    $this->mode = basename(rtrim($url['path'], '/'));
  }
  
  public function showTemporayForm()
  {
    $this->render(APP_PATH . '/Views/user/register.php', [
      'mode' => $this->mode,
      'title' => '仮登録'
    ]);
  }

  public function storeTemporary()
  {
    if (! $this->validator->tempInputValidate($this->request)) {
        $this->setSessionErrorsAndOld($this->validator);
        redirect(url('/register/temporary'));
    }
    if ($this->userRepo->existsByEmail($this->request['email']) > 0) {
        $this->session->set('errors.email', "このメールアドレスは本登録済です");
        $this->session->set('old.email', $this->request['email']);
        redirect(url('/register/temporary'));
    }
    if ($this->tempRepo->isBeforeExpired($this->request['email']) > 0) {
        $this->session->set('errors.email', "このアドレスは有効です、メールをご確認下さい");
        $this->session->set('old.email', $this->request['email']);
        redirect(url('/register/temporary'));
    }
    $urlToken = $this->registerService->storeTemporaryUser($this->request);
    $this->session->set('url_token', $urlToken);
    redirect(url('/register/verify'));
  }

  public function showVerifyForm()
  {
    $urlToken = $this->session->get('url_token') ?? '';
    $toRegisterLink = 'register/profile?url_token=' . $urlToken;
    $this->session->forget('url_token');

    $this->render(APP_PATH . '/Views/user/email-alternative.php', [
      'mode' => $this->mode,
      'toRegisterLink' => $toRegisterLink,
      'title' => '登録のご案内',
    ]);
  }

  public function showProfileForm()
  {
    $email = $this->registerService->verifyToken($this->request['url_token'] ?? '');
    if (! $email) {
      $this->session->set('errors.register', '未登録か期限切れの様です。始めからお手続きください');
      redirect(url('/register/temporary'), 302);
    }
    $this->session->set('url_token', $this->request['url_token']);

    $this->render(APP_PATH . '/Views/user/register.php', [
      'mode' => $this->mode,
      'email' => $email,
      'urlToken' => $this->request['url_token'],
      'title' => '本登録',
    ]);

  }
  public function storeProfile()
  {
    if (! $this->validator->registerValidate($this->request)) {
      $this->setSessionErrorsAndOld($this->validator);
      redirect(url('/register/profile' . '?url_token=' . ($this->session->get('url_token') ?? '')));
    }
    
    $inputs = only(['firstname', 'lastname', 'firstnamekana', 'lastnamekana',
             'email', 'phone', 'gender', 'password'], $this->request);
    $this->session->set('register', $inputs);

    redirect(url('/register/confirm'));
  }

  public function showConfirm()
  {
    $inputs = $this->session->get('register');
    $backUrl = '/register/profile?url_token=' . ($this->session->get('url_token') ?? '');

    $this->render(APP_PATH . '/Views/user/register.php', [
      'mode' => $this->mode,
      'inputs' => $inputs,
      'backUrl' => $backUrl,
      'title' => '登録内容のご確認',
    ]);
  }

  public function submit()
  {
    if (! $this->registerService->verifyToken($this->session->get('url_token'))) {
      $this->session->set('errors.register', '未登録か期限切れの様です。始めからお手続きください');
      $this->session->forgetMulti(['register', 'url_token']);
      redirect(url('/register/temporary'), 302);
    }
    $inputs = $this->session->get('register');
    $this->registerService->registerUser($inputs);

    $this->session->forgetMulti(['register', 'url_token']);

    redirect(url('/register/complete'));
  }

  public function complete()
  {
    $this->render(APP_PATH . '/Views/user/register.php', [
      'mode' => $this->mode,
      'title' => '登録完了',
    ]);
  }
}