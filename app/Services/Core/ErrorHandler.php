<?php
namespace App\Services\Core;

use App\Services\Core\ExceptionMapper;
use App\Services\Core\Logger;
use App\Utils\RequestHelper;
use ErrorException;
use Throwable;

class ErrorHandler
{
  public function __construct(
    private Logger $logger, 
    private bool $debug = false,
  ){}

  public function handle(Throwable $e): void
  {
    // favicon　error　の出力させない
    $uri = normalizeUri(parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH));
    if ($uri === '/favicon.ico' || str_starts_with($uri, '/.well-known/appspecific')) {
      return;
    }

    $this->logger->error($e->getMessage(), [
      'trace' => $e->getTraceAsString(),
      'type' => get_class($e)
    ]);

    // レスポンス文字列を作成
    $response = $this->renderResponse($e);

    // 実際の出力
    echo $response;
    exit;
  }

  private function renderResponse(Throwable $e) :string
  {
    [$message, $code] = ExceptionMapper::resolve($e);
    http_response_code($code);

    if (RequestHelper::isAjaxOrApi()) {
      header('Content-Type: application/json; charset=utf-8');
      return json_encode([
        'success' => false,
        'message' => $message,
      ], JSON_UNESCAPED_UNICODE);
    }

    // 開発環境では詳細なスタックトレースを表示
    if ($this->debug) {
      return $this->renderDebug($e);
    }

    // 本番環境ではユーザー向けエラーページに振り分け
    ob_start();
    $errorMessage = $message;
    $errorCode = $code;
    require BASE_PATH . '/app/Views/errors/exception-error.php';
    return ob_get_clean();
  }

  private function renderDebug(Throwable $e): string
  {
    $level = $this->mapSeverity($e);

    return <<<HTML
    <h3>Debug Error</h3>
    <p><strong>Type:</strong> {$level}</p>
    <p><strong>Message:</strong> {$e->getMessage()}</p>
    <pre>{$e->getTraceAsString()}</pre>
    HTML;
  }

  private function mapSeverity(Throwable $e): string
  {
    if ($e instanceof ErrorException) {
        return match($e->getSeverity()) {
            E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR => 'Fatal Error',
            E_WARNING, E_USER_WARNING                            => 'Warning',
            E_NOTICE, E_USER_NOTICE                              => 'Notice',
            E_DEPRECATED, E_USER_DEPRECATED                      => 'Deprecated',
            default => 'Error',
        };
    }
    // その他のThrowable（TypeError, ParseErrorなど）
    return match (true) {
        $e instanceof \TypeError     => 'Fatal Error',
        $e instanceof \ParseError    => 'Fatal Error',
        $e instanceof \Error         => 'Fatal Error',
        default                      => 'Error',
    };
  }
}