<?php
namespace App\Services\External;

use Carbon\Carbon;
use Exception;

class OpenWeatherService
{
  public function __construct(
    private OpenWeatherClient $client,
    private OpenWeatherCache $cache)
  {
  }

  public function getWeatherByDate(string $date): array
  {
    if(! $this->cache->isExpired($date)) {
      $cached = $this->cache->get($date);
      if ($cached !== null) {
        return $cached;
      }
    }

    try {
      $forecastList = $this->client->fetchForcast();
    } catch(Exception $e) {
      $cached = $this->cache->get($date);
      return $cached ?? [];
    }

    $targetDate = Carbon::parse($date)->format('Y-m-d');
    $hours = ['09', '12', '15', '18', '21'];

    $filtered = array_filter($forecastList, function($item) use($targetDate, $hours) {
      $dt = Carbon::createFromTimestamp($item['dt']);
      return $dt->format('Y-m-d') === $targetDate
        && in_array($dt->format('H'), $hours, true);
    });

    $result = array_values($filtered);

    $this->cache->put($date, $result);

    return $result;
  }

}