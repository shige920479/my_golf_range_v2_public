<?php
namespace App\Controller;

use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;

abstract class BaseController
{
  protected array $request;
  protected string $csrfToken;
  protected bool $isLoggedIn;

  public function __construct(
    protected SessionService $session,
    protected RequestHandler $requestHandler,
    protected TokenManager $tokenManager,
    protected Logger $logger,
    protected ErrorHandler $errorHandler,
    protected DbConnect $db,
    protected AuthGuard $authGuard,
  )
  {
    $this->request = $this->requestHandler->getRequest();
    $this->csrfToken = $this->tokenManager->get();
    $this->isLoggedIn = isset($_SESSION['user']['id']) && isset($_SESSION['user']['email']);
  }

  public function render(string $viewPath, array $data = []): void
  {
    extract($data);
    $csrfToken = $this->csrfToken;
    $session = $this->session;
    $isLoggedIn = $this->isLoggedIn;
    $isConfirm = preg_match('#^/reservation(?:/\d+)?/confirm$#', normalizeUri(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH)));

    include $viewPath;
  }
  
  public function setSessionErrorsAndOld(object $validator): void
  {
      $this->session->set('errors', $validator->getErrors());
      $this->session->set('old', $validator->getOld());
  }

  public function setRequest(array $data): void
  {
      $this->request = $data;
  }
}
