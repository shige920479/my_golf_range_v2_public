<?php
namespace App\Controller\User;

use App\Controller\BaseController;
use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;
use App\Services\User\MypageService;

class MypageController extends BaseController
{
  private MypageService $mypageService;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    MypageService $mypageService,
  )
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard);
    $this->mypageService = $mypageService;
  }

  public function index()
  {
    $userId = $_SESSION['user']['id'];
    $data = $this->mypageService->getMyReservation($userId);

    $this->render(APP_PATH . '/Views/user/mypage.php', [
      'reservations' => $data['reservations'],
      'today' => $data['today'],
      'expired' => $data['expired'],
      'title' => '予約情報'
    ]);
  }

  

}