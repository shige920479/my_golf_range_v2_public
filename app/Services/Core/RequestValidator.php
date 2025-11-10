<?php
namespace App\Services\Core;

use App\Exceptions\BadRequestException;
use App\Utils\RequestHelper;

class RequestValidator
{
  public function __construct(private array $skipModes){}

  public function validateToken(array $post): void
  {
    $mode = $post['mode'] ?? '';
    if (in_array($mode, $this->skipModes, true)) return;

    $headers = $this->getNormalizedHeaders();

    $postToken = $post['token'] ?? null;
    $headerToken = $headers['x-csrf-token'] ?? null;
    $sessionToken = $_SESSION['token'] ?? null;

    $incomingToken = ! empty($postToken) ? $postToken : $headerToken;
    
    if (empty($sessionToken) || $sessionToken !== $incomingToken) {
      unset($_SESSION['token']);
      throw new BadRequestException(
        "トークンエラー不整合: POST-TOKEN = {$postToken} / HEADER-TOKEN = {$headerToken} / SESSION-TOKEN = {$sessionToken}"
      );
    }
  }

  /**
   * 環境依存せずにヘッダーを取得し小文字キーに統一
   * - `getallheaders()` が利用可能な場合はそれを使用
   * - 利用不可な場合は $_SERVER から再構築
   * - ヘッダー名は大文字小文字の区別やアンダースコア/ハイフンの差異を吸収
   *
   * @return array<string,string> 小文字化されたヘッダーキーと値の連想配列
   */
  private function getNormalizedHeaders(): array
  {
    if (function_exists('getallheaders')) {
      $headers = getallheaders();
    } else {
      $headers = [];
      foreach($_SERVER as $key => $value) {
        if (str_starts_with($key, 'HTTP_')) {
          $header = str_replace('_', '-', substr($key, 5));
          $headers[$header] = $value;
        }
      }
    }

    $normalized = [];
    foreach($headers as $key => $value) {
      $normalized[strtolower($key)] = $value;
    }

    return $normalized;
  }
}