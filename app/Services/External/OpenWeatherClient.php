<?php
namespace App\Services\External;

use Exception;

class OpenWeatherClient
{
  private string $baseUrl = 'https://api.openweathermap.org/data/2.5/forecast';
  private string $apiKey;
  private int $cityId;

  public function __construct(?string $apiKey = null, ?int $cityId = null)
  {
    $this->apiKey = $apiKey ?? WEATHER_API_KEY;
    $this->cityId = $cityId ?? 1859740;;
  }

  /** 天気予報を取得 */
  public function fetchForcast(): array
  {
    $url = sprintf('%s?id=%d&appid=%s&lang=ja&units=metric', $this->baseUrl, $this->cityId, $this->apiKey);

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response =  curl_exec($ch);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($response === false) {
      throw new Exception("OpenWeather接続エラー: {$error}");
    }

    $decoded = json_decode($response, true);
    if (! isset($decoded['list'])) {
      throw new Exception('無効な API 応答: リストキーがありません');
    }

    return $decoded['list'];
  }
}