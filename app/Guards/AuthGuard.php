<?php
namespace App\Guards;

use App\Exceptions\BadRequestException;
use App\Services\Core\ErrorHandler;
use App\Services\Core\SessionService;
use App\Utils\RequestHelper;

class AuthGuard
{
  public const ROLE_ADMIN = 'admin';
  public const ROLE_OWNER = 'owner';
  public const ROLE_USER = 'user';

  public function __construct(
    private SessionService $session,
    private ErrorHandler $errorHandler
  )
  {
  }

  public function checkAuth(string $authenticator): void
  {
    switch ($authenticator) {

      case self::ROLE_ADMIN:
        if (empty($this->session->get('admin'))) {
          if (RequestHelper::isAjax()) {
            echo json_encode([
              'success' => false,
              'message' => 'ログインが必要です'
            ]);
            exit;
          }
          redirect(url('/admin/login'));
        }
        break;

      case self::ROLE_OWNER :
        if (empty($this->session->get('owner'))) {
          if (RequestHelper::isAjax()) {
            http_response_code(401);
            echo json_encode([
              'success' => false,
              'message' => 'ログインが必要です'
            ]);
            exit;
          }
          redirect(url('/owner/login'));
        }
        break;

      case self::ROLE_USER :
        if (empty($this->session->get('user'))) {
          if (RequestHelper::isAjax()) {
            http_response_code(401);
            echo json_encode([
              'success' => false,
              'message' => 'ログインが必要です'
            ]);
            exit;
          }
          redirect(url('/login'));
        }
        break;

      default :
        throw new BadRequestException("不正なアクセス : {$authenticator}");
        break;
    }
  }

  public function redirectIfAuthenticated(string $authenticator): void
  {
    switch ($authenticator) {
      case self::ROLE_ADMIN :
        if (! empty($_SESSION['admin'])) redirect(url('/admin/home'));
        break;
      
      case self::ROLE_OWNER :
        if (! empty($_SESSION['owner'])) redirect(url('/owner/home'));
          break;
      
      case self::ROLE_USER :
        if (! empty($_SESSION['user'])) redirect(url('/reservation'));
        break;
      
      default :
        throw new BadRequestException("不正なアクセス : {$authenticator}");
        break;
    }
  }
}