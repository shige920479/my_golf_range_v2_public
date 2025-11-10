<?php
namespace App\Services\Validation;

use Carbon\Carbon;
use DateTime;

trait FormRulesTrait
{
  protected function required(string $field, string $value): bool
  {
    if (empty($value) || trim($value) === '') {
      $this->errors[$field] = '必須事項です、入力願います';
      return false;
    } else {
      if (! in_array($field, ['password', 'password_confirmation'])) {
        $this->old[$field] = $value;
      }
      return true;
    }
  }

  protected function radioOptions(string $field, string $value, array $validOptions): bool
  {
    if (! isset($value) || ! in_array($value, $validOptions)) {
      $this->errors[$field] = "有効な選択肢を選んでください";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function maxLength(string $field, string $value, int $maxLength): bool
  {
    if (mb_strlen($value, 'UTF-8') > $maxLength) {
      $this->errors[$field] = "{$maxLength}文字以内で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function minLength(string $field, string $value, int $minLength): bool
  {
    if (mb_strlen($value, 'UTF-8') < $minLength) {
      $this->errors[$field] = "{$minLength}文字以上で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function numeric(string $field, mixed $value): bool
  {
    if (! preg_match('/\A[1-9][0-9]*\z/', $value)) {
      $this->errors[$field] = "数値で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }
  protected function integer(string $field, mixed $value): bool
  {
    if (! filter_var($value, FILTER_VALIDATE_INT, ['options' => ['max_range' => 10000]]) || ! $value > 0) {
      $this->errors[$field] = "1～9999の範囲の整数で入力願います";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function email(string $field, string $value): bool
  {
    if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
      $this->errors[$field] = "メールアドレスの形式が正しくありません";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function phoneNumber(string $field, string $value): bool
  {
    $normalized = preg_replace('/[^\d]/', '', $value);
    if (! preg_match('/^(0\d{9,10})$/', $normalized)) {
      $this->errors[$field] = "電話番号の形式が正しくありません";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function gender(string $field, $value): bool
  {
    if (! in_array($value, ['male', 'female', 'other'])) {
      $this->errors[$field] = "選択が正しくありません";
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function password(string $field, string $value): bool
  {
    if (! preg_match("/\A(?=.*?[A-z])(?=.*?\d)[A-z\d]{8,12}+\z/", $value)) {
      $this->errors[$field] = "8～12文字の半角英数字で入力願います";
      return false;
    }
    return true;
  }

  protected function confirmed(string $field, string $value, string $confirm_value): bool
  {
    if ($value !== $confirm_value) {
      $this->errors[$field] = '確認用と一致していません、再入力願います';
      return false;
    }
    return true;
  }

  protected function validBoolean(string $field, mixed $value)
  {
    if (! in_array((int)$value, [0, 1], true)) {
      $this->errors[$field] = '無効な値です';
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }

  protected function dateForm(string $field, string $value): bool
  {
    $result = false;
    $result = preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) === 1;
    if ($result) {
      [$year, $month, $day] = explode('-', $value);
      $result = checkdate((int)$month, (int)$day, (int)$year);
    }

    if (! $result) {
      $this->errors[$field] = '日付形式が異なっています';
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }
  protected function timeForm(string $field, string $value): bool
  {
    $time = DateTime::createFromFormat('H:i:s', $value);
    $result = $time && $time->format('H:i:s') === $value;
    if (! $result) {
      $this->errors[$field] = '時刻形式が異なっています';
      return false;
    } else {
      $this->old[$field] = $value;
      return true;
    }
  }


  public function getErrors()
  {
    return $this->errors;
  }
  public function getOld()
  {
    return $this->old;
  }

}





