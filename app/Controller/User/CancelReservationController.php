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
use App\Services\User\ReserveService;

class CancelReservationController extends BaseController
{
  private ReserveService $reserveService;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    ReserveService $reserveService)
  {
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard);
    $this->reserveService = $reserveService;
    $this->authGuard->checkAuth('user');
  }

  /** 予約キャンセル */
  public function delete(int $id)
  {
    $this->reserveService->checkOwner($id);
    $this->reserveService->cancelReservation($id);

    redirect(url('/mypage'));
  }

}