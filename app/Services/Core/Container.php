<?php
namespace App\Services\Core;

use App\Controller\DataController;
use App\Controller\HomeController;
use App\Controller\Owner\AuthController as OwnerAuthController;
use App\Controller\Owner\InitRegisterController;
use App\Controller\Owner\SettingController;
use App\Controller\User\AuthController;
use App\Controller\User\CancelReservationController;
use App\Controller\User\CreateReservationController;
use App\Controller\User\MypageController;
use App\Controller\User\RegisterController;
use App\Controller\User\UpdateReservationController;
use App\Database\DbConnect;
use App\Dev\Controller\TestController;
use App\Guards\AuthGuard;
use App\Repositories\FeeRepository;
use App\Repositories\OwnerRepository;
use App\Repositories\RangeRepository;
use App\Repositories\RentalRepository;
use App\Repositories\ReserveRangeRepository;
use App\Repositories\ReserveRentalRepository;
use App\Repositories\ReserveShowerRepository;
use App\Repositories\ShowerRepository;
use App\Repositories\TemporaryUserRepository;
use App\Repositories\UserRepository;
use App\Services\Auth\UserLoginService;
use App\Services\Auth\UserRegisterService;
use App\Services\Owner\InitRegisterService;
use App\Services\Owner\SettingService;
use App\Services\Security\PasswordService;
use App\Services\Security\TokenManager;
use App\Services\User\CalcFeeService;
use App\Services\User\MypageService;
use App\Services\User\ReserveFormService;
use App\Services\User\ReserveService;
use App\Services\User\ReserveTableService;
use App\Services\Validation\Facility\InitValidation;
use App\Services\Validation\Facility\SettingValidation;
use App\Services\Validation\Reserve\FacilityValidation;
use App\Services\Validation\Reserve\FormValidation;
use App\Services\Validation\Reserve\ReservableValidation;
use App\Services\Validation\Reserve\TimeValidation;
use App\Services\Validation\UserLoginValidation;
use App\Services\Validation\UserRegisterValidaion;


class Container
{
  private ?SessionService $sessionService = null;
  private ?RequestHandler $requestHandler = null;
  private ?TokenManager $tokenManager = null;
  private ?DbConnect $db = null;
  private ?Logger $logger = null;
  private ?ErrorHandler $errorHandler = null;
  private ?AuthGuard $authGuard = null;
  private ?FeeRepository $feeRepo = null;
  private ?TemporaryUserRepository $temporaryUserRepo = null;
  private ?UserRepository $userRepo = null;
  private ?UserRegisterService $userRegisterService = null;
  private ?UserLoginService $userLoginService = null;
  private ?ReserveService $reserveService = null;
  private ?RangeRepository $rangeRepo = null;
  private ?RentalRepository $rentalRepo = null;
  private ?ShowerRepository $showerRepo = null;
  private ?ReserveRangeRepository $reserveRangeRepo = null;
  private ?ReserveRentalRepository $reserveRentalRepo = null;
  private ?ReserveShowerRepository $reserveShowerRepo = null;
  private ?SettingService $settingService = null;
  private ?OwnerRepository $ownerRepo = null;
  private ?InitRegisterService $initRegisterService = null;

  private function getSessionService(): object
  {
    return $this->sessionService ??= new SessionService();
  }

  private function getRequestHandler(): object
  {
    if ($this->requestHandler === null) {
        $skipModes = include(APP_PATH . '/config/request.php');
        $this->requestHandler = new RequestHandler(new RequestValidator($skipModes));
    }
    return $this->requestHandler;
  }

  private function getTokenManager(): object
  {
    return $this->tokenManager ??= new TokenManager();
  }

  private function getDb(): object
  {
    return $this->db ??= new DbConnect();
  }

  private function getLogger(): object
  {
    $debug = ! isProduction();
    return $this->logger ??= new Logger(BASE_PATH . '/app/log/app.log', $debug);
  }

  private function getErrorHandler(): object
  {
    $debug = ! isProduction();
    return $this->errorHandler ??= new ErrorHandler($this->getLogger(), $debug);
  }

  private function getAuthGuard(): object
  {
    return $this->authGuard ??= new AuthGuard(
      $this->getSessionService(), $this->getErrorHandler()
    );
  }

  private function getFeeRepo(): object
  {
    return $this->feeRepo ??= new FeeRepository($this->getDb());
  }

  private function getTemporaryUserRepo(): object
  {
    return $this->temporaryUserRepo ??= new TemporaryUserRepository($this->getDb());
  }
  private function getUserRepo(): object
  {
    return $this->userRepo ??= new UserRepository($this->getDb());
  }
  private function getOwnerRepo(): object
  {
    return $this->ownerRepo ??= new OwnerRepository($this->getDb());
  }

  private function getUserRegisterService(): object
  {
    return $this->userRegisterService ??= new UserRegisterService(
      $this->getTemporaryUserRepo(), $this->getUserRepo(), new PasswordService(), $this->getDb());
  }
  private function getUserLoginService(): object
  {
    return $this->userLoginService ??= new UserLoginService(
      $this->getUserRepo(), $this->getOwnerRepo(), new PasswordService());
  }

  private function getRangeRepo(): object
  {
    return $this->rangeRepo ??= new RangeRepository($this->getDb());
  }
  private function getRentalRepo(): object
  {
    return $this->rentalRepo ??= new RentalRepository($this->getDb());
  }
  private function getShowerRepo(): object
  {
    return $this->showerRepo ??= new ShowerRepository($this->getDb());
  }
  private function getReserveRangeRepo(): object
  {
    return $this->reserveRangeRepo ??= new ReserveRangeRepository($this->getDb());
  }
  private function getReserveRentalRepo(): object
  {
    return $this->reserveRentalRepo ??= new ReserveRentalRepository($this->getDb());
  }
  private function getReserveShowerRepo(): object
  {
    return $this->reserveShowerRepo ??= new ReserveShowerRepository($this->getDb());
  }
  private function getReserveService(): object
  {
    return $this->reserveService ??= new ReserveService(
      $this->getRangeRepo(), $this->getRentalRepo(),$this->getShowerRepo(),
      $this->getReserveRangeRepo(), $this->getReserveRentalRepo(), $this->getReserveShowerRepo(),
      new ReserveFormService($this->getRangeRepo(), $this->getRentalRepo(),$this->getShowerRepo()),
      new ReserveTableService(), $this->getFeeRepo(), $this->getDb()
    );
  }
  private function getSettingService(): object
  {
    return $this->settingService ??= new SettingService(
      $this->getFeeRepo(), $this->getRangeRepo(), $this->getRentalRepo(),$this->getShowerRepo(),
    );
  }
  private function getInitRegisterService(): object
  {
    return $this->initRegisterService ??= new InitRegisterService(
      $this->getRangeRepo(), $this->getRentalRepo(),$this->getShowerRepo(), $this->getFeeRepo(), 
    );
  }

  public function get(string $class): object
  {
    $commonDeps = [
      $this->getSessionService(),
      $this->getRequestHandler(),
      $this->getTokenManager(),
      $this->getLogger(),
      $this->getErrorHandler(),
      $this->getDb(),
      $this->getAuthGuard(),
    ];

    return match ($class) {
        // 個別依存があるコントローラー
        TestController::class =>
            new $class(
              ...$commonDeps,
              ),
        DataController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [new PasswordService(),
                ]
              )
            ),
        HomeController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [$this->getFeeRepo(),
                ]
              )
            ),
        RegisterController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  new UserRegisterValidaion(),
                  $this->getUserRegisterService(),
                  $this->getTemporaryUserRepo(),
                  $this->getUserRepo(),
                ]
              )
            ),
        AuthController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  new UserLoginValidation(),
                  $this->getUserLoginService(),
                ]
              )
            ),
        CreateReservationController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  $this->getReserveService(),
                  new FormValidation(),
                  new FacilityValidation($this->getRangeRepo(), $this->getRentalRepo(), $this->getShowerRepo()),
                  new TimeValidation(),
                  new ReservableValidation($this->getReserveRangeRepo(), $this->getReserveRentalRepo(), $this->getReserveShowerRepo()),
                  new CalcFeeService($this->getFeeRepo()),
                ]
              )
            ),
        UpdateReservationController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  $this->getReserveService(),
                  new FormValidation(),
                  new FacilityValidation($this->getRangeRepo(), $this->getRentalRepo(), $this->getShowerRepo()),
                  new TimeValidation(),
                  new ReservableValidation($this->getReserveRangeRepo(), $this->getReserveRentalRepo(), $this->getReserveShowerRepo()),
                  new CalcFeeService($this->getFeeRepo()),
                ]
              )
            ),
        CancelReservationController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  $this->getReserveService(),
                ]
              )
            ),
        MypageController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  new MypageService($this->getReserveRangeRepo()),
                ]
              )
            ),
        OwnerAuthController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  new UserLoginValidation(),
                  $this->getUserLoginService(),
                ]
              )
            ),
        SettingController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  $this->getSettingService(),
                  new SettingValidation(),
                ]
              )
            ),
        InitRegisterController::class =>
            new $class(
              ...array_merge(
                $commonDeps,
                [
                  new InitValidation(),
                  new SettingValidation(),
                  $this->getInitRegisterService(),
                ]
              )
            ),
        // 共通依存だけで足りるコントローラー
        default => new $class(...$commonDeps),
    };
  }
}
