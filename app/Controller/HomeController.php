<?php
namespace App\Controller;

use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;
use App\Repositories\FeeRepository;

class HomeController extends BaseController
{
  private FeeRepository $feeRepo;

  public function __construct(
    SessionService $session,
    RequestHandler $requestHandler,
    TokenManager $tokenManager,
    Logger $logger,
    ErrorHandler $errorHandler,
    DbConnect $db,
    AuthGuard $authGuard,
    FeeRepository $feeRepo 
  ){
    parent::__construct($session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard);
    $this->feeRepo = $feeRepo;
  }

  public function index(): void
  {
    $this->render(APP_PATH . '/Views/user/home.php');
  }

  public function showPriceList(): void
  {
    $tables = ['rangeFee', 'rentalFee', 'showerFee'];
    
    foreach($tables as $table) {
      $current[$table] = $this->feeRepo->getCurrentFee($table);
      $change[$table] = $this->feeRepo->getChangeFee($table) ?: null;
    }

    $this->render(APP_PATH . '/Views/user/price-list.php', [
      'current' => $current,
      'change' => $change,
      'title' => '料金表'
    ]);
  }

}