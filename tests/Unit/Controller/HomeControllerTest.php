<?php

use App\Controller\HomeController;
use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Repositories\FeeRepository;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class HomeControllerTest extends TestCase
{
  #[Test]
  public function showPriceList_renders_current_and_change_fees(): void
  {
    $repo = $this->createMock(FeeRepository::class);
    $repo = $this->createMock(FeeRepository::class);

    $repo->method('getCurrentFee')->willReturnMap([
        ['rangeFee', ['entrance_fee' => 100, 'weekday_fee' => 200, 'holiday_fee' => 300, 'effective_date' => '2025-09-01']],
        ['rentalFee', ['rental_fee' => 400, 'effective_date' => '2025-09-01']],
        ['showerFee', ['shower_fee' => 500, 'effective_date' => '2025-09-01']],
    ]);

    $repo->method('getChangeFee')->willReturnMap([
        ['rangeFee', ['entrance_fee' => 150, 'weekday_fee' => 250, 'holiday_fee' => 350, 'effective_date' => '2025-10-01']],
        ['rentalFee', ['rental_fee' => 450, 'effective_date' => '2025-10-01']],
        ['showerFee', ['shower_fee' => 550, 'effective_date' => '2025-10-01']],
    ]);

    $session = $this->createMock(SessionService::class);
    $requestHandler = $this->createMock(RequestHandler::class);
    $tokenManager = $this->createMock(TokenManager::class);
    $logger = $this->createMock(Logger::class);
    $errorHandler = $this->createMock(ErrorHandler::class);
    $db = $this->createMock(DbConnect::class);
    $authGuard = $this->createMock(AuthGuard::class);

    $controller = new HomeController(
      $session, $requestHandler, $tokenManager, $logger, $errorHandler, $db, $authGuard, $repo
    );

    ob_start();
    $controller->showPriceList();
    $output = ob_get_clean();

    $this->assertStringContainsString(100, $output);
    $this->assertStringContainsString(400, $output);
    $this->assertStringContainsString(500, $output);
    $this->assertStringContainsString(150, $output);
    $this->assertStringContainsString(450, $output);
    $this->assertStringContainsString(550, $output);
  }

}