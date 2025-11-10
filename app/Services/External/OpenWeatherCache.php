<?php
namespace App\Services\External;

class OpenWeatherCache
{
  private string $cacheDir;
  private int $expireSeconds;
  private int $cleanUpDays;

  public function __construct(?string $cacheDir = null, ?int $expireSeconds = null, ?int $cleanUpDays = null)
  {
    $this->expireSeconds = $expireSeconds ?? WEATHER_CACHE_EXPIRE;
    $this->cleanUpDays = $cleanUpDays ?? WEATHER_CACHE_CLEANUP_DAYS;

    $this->cacheDir = $cacheDir ?? BASE_PATH . '/storage/cache';
    if (! is_dir($this->cacheDir)) {
      mkdir($this->cacheDir, 0777, true);
    }

    $this->cleanOldCache();
  }

  /** キャッシュファイルパスの生成 */
  private function getCachePath(string $date): string
  {
    return "{$this->cacheDir}/weather_{$date}.json";
  }

  /** キャッシュを取得 */
  public function get(string $date): ?array
  {
    $path = $this->getCachePath($date);
    if (! file_exists($path)) {
      return null;
    }

    $data = json_decode(file_get_contents($path), true);
    if (! is_array($data)) {
      return null;
    }

    return $data;
  }
  
  /** キャッシュを保存（JSON形式で書き出す） */
  public function put(string $date, array $data): void
  {
    $path = $this->getCachePath($date);
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT, JSON_UNESCAPED_UNICODE));
  }

  /** キャッシュの有効期限チェック（デフォルト 3時間） */
  public function isExpired(string $date): bool
  {
    $path = $this->getCachePath($date);
    if (! file_exists($path)) {
      return true;
    }

    if (filemtime($path) + $this->expireSeconds < time()) {
      unlink($path);
      return true;
    }

    return false;
  }

  /** 古いキャッシュを自動削除（初期化時に呼び出す）デフォルトは10日以前 */
  private function cleanOldCache(): void
  {
    foreach (glob($this->cacheDir . '/*.json') as $file) {
      if (filemtime($file) < strtotime("-{$this->cleanUpDays} days")) {
        unlink($file);
      }
    }
  }

}