<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Services\Auth\UserLoginService;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;
use App\Services\Validation\UserLoginValidation;

class AuthController extends BaseController
{
  private UserLoginValidation $validator;
  private UserLoginService $loginService;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    UserLoginValidation $validator,
    UserLoginService $loginService,
  )
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard);
    $this->validator = $validator;
    $this->loginService = $loginService;
  }

  public function loginForm()
  {
    $this->authGuard->redirectIfAuthenticated($this->authGuard::ROLE_USER);
    $this->render(APP_PATH . '/Views/user/login.php');
  }

  public function login()
  {
    if (! $this->validator->loginValidate($this->request)) {
      $this->setSessionErrorsAndOld($this->validator);
      redirect(url('/login'));
    }

    list($email, $password) = [$this->request['email'], $this->request['password']];
    if (! $this->loginService->find($email)) {
      $this->session->set('errors.email', 'アカウント登録がありません');
      redirect(url('/login'));
    }
    $user = $this->loginService->verify($email, $password);
    if (! $user) {
      $this->session->set('errors.password', 'パスワードが一致していません');
      $this->session->set('old.email', $email);
      redirect(url('/login'));
    }

    $this->session->forget('old');
    $this->session->loginSessionGenerate($user, 'user');
    redirect(url('/reservation'));
  }

  public function logout()
  {
    $this->session->logoutSession();
    redirect(url('/'));
  }

}