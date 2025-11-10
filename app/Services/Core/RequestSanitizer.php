<?php
namespace App\Services\Core;

class RequestSanitizer
{
  public static function normalize(array $data): array
  {
    $normalized = [];
    foreach($data as $key => $value) {
      if (is_array($value)) {
        $normalized[$key] = self::normalize($value);
      } else {
        $val = is_string($value) ? trim($value) : $value;
        $normalized[$key] = ($val === '' ? null : $val);
      }
    }
    return $normalized;
  }
}

// sanitizerは削除したので、次のメソッドから再開