<?php
require_once __DIR__ . '/../../helpers/ReflectionHelper.php';
use App\Controller\BaseController;
use App\Database\DbConnect;
use App\Guards\AuthGuard;
use App\Services\Core\ErrorHandler;
use App\Services\Core\Logger;
use App\Services\Core\RequestHandler;
use App\Services\Core\SessionService;
use App\Services\Security\TokenManager;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BaseControllerTest extends TestCase
{
  use ReflectionHelper;

  private $sessionMock;
  private $requestHandlerMock;
  private $tokenManagerMock;
  private Logger $loggerMock;
  private ErrorHandler $errorHandlerMock;
  private DbConnect $db;
  private $authGuard;

  protected function setUp(): void
  {
      $this->sessionMock = $this->createMock(SessionService::class);
      $this->requestHandlerMock = $this->createMock(RequestHandler::class);
      $this->tokenManagerMock = $this->createMock(TokenManager::class);
      $this->loggerMock = $this->createMock(Logger::class);
      $this->errorHandlerMock = $this->createMock(ErrorHandler::class);
      $this->db = $this->createMock(DbConnect::class);
      $this->authGuard = $this->createMock(AuthGuard::class);
      
      $_SERVER['REQUEST_URI'] = '/reservation/confirm';
  }

  protected function tearDown(): void
  {
    $_SERVER['REQUEST_URI'] = '';
  }

  #[Test]
  public function it_initialize_request_and_token(): void
  {
    $expectedRequest = ['name' => 'taro'];
    $expectedToken = 'token123';

    $this->requestHandlerMock->expects($this->once())->method('getRequest')
      ->willReturn($expectedRequest);
    
    $this->tokenManagerMock->expects($this->once())->method('get')
     ->willReturn($expectedToken);

    $controller = new class(
      $this->sessionMock,
      $this->requestHandlerMock,
      $this->tokenManagerMock,
      $this->loggerMock,
      $this->errorHandlerMock,
      $this->db,
      $this->authGuard
    ) extends BaseController {};

    $reqProp = $this->getProperty($controller, 'request');
    $this->assertSame($expectedRequest, $reqProp);
    
    $tokenProp = $this->getProperty($controller, 'csrfToken');
    $this->assertSame($expectedToken, $tokenProp);
  }

  #[Test]
  public function render_includes_view_and_extracts_data(): void
  {
    $this->requestHandlerMock->method('getRequest')->willReturn([]);
    $this->tokenManagerMock->method('get')->willReturn('test_token');

    $controller = new class(
      $this->sessionMock,
      $this->requestHandlerMock,
      $this->tokenManagerMock,
      $this->loggerMock,
      $this->errorHandlerMock,
      $this->db,
      $this->authGuard
    ) extends BaseController {};

    $viewPath = __DIR__ . '/tmp/view.php';
    file_put_contents($viewPath, '<?php echo $message . "-" . $csrfToken; ?>');

    ob_start();
    $controller->render($viewPath, ['message' => 'Hello']);
    $output = ob_get_clean();

    $this->assertSame('Hello-test_token', $output);

    unlink($viewPath);
  }

  #[Test]
  public function it_initialized_is_logged_in(): void
  {
    $_SESSION['user']['id'] = 1;
    $_SESSION['user']['email'] = 'test@mail.com';

    $controller = new class(
      $this->sessionMock,
      $this->requestHandlerMock,
      $this->tokenManagerMock,
      $this->loggerMock,
      $this->errorHandlerMock,
      $this->db,
      $this->authGuard
    ) extends BaseController {};

    $prop = $this->getProperty($controller, 'isLoggedIn');
    $this->assertSame(true, $prop);

    $_SESSION = [];
  }
  #[Test]
  public function it_initialized_is_not_logged_in(): void
  {
    $_SESSION['user']['id'] = 1;
    $controller = new class(
      $this->sessionMock,
      $this->requestHandlerMock,
      $this->tokenManagerMock,
      $this->loggerMock,
      $this->errorHandlerMock,
      $this->db,
      $this->authGuard
    ) extends BaseController {};

    $prop = $this->getProperty($controller, 'isLoggedIn');
    $this->assertSame(false, $prop);
    $_SESSION = [];
  }



}