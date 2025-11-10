<?php

use App\Services\Validation\FormRulesTrait;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class FormRulesTraitTest extends TestCase
{
  private $class;

  protected function setUp(): void
  {
    $this->class = new class {
      use FormRulesTrait;

      public array $errors = [];
      public array $old = [];

      public function call(string $method, ...$args)
      {
        return $this->{$method}(...$args);
      }
  
      public function getErrors(): array
      {
        return $this->errors;
      }
  
      public function getOld(): array
      {
        return $this->old;
      }
    };
  }

  #[Test]
  public function required_with_valid_value(): void
  {
    $result = $this->class->call('required', 'name', 'taro');
    $this->assertTrue($result);
    $this->assertSame('taro', $this->class->getOld()['name']);
    $this->assertArrayNotHasKey('name', $this->class->getErrors());
  }

  #[Test]
  public function required_with_empty_value(): void
  {
    $result = $this->class->call('required', 'email', '');
    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->class->getErrors());
    $this->assertSame('必須事項です、入力願います', $this->class->getErrors()['email']);
  }
  #[Test]
  public function radioOptions_with_valid_value(): void
  {
    $result = $this->class->call('radioOptions', 'gender', 'male', ['male', 'female', 'others']);
    $this->assertTrue($result);
    $this->assertSame('male', $this->class->getOld()['gender']);
    $this->assertArrayNotHasKey('gender', $this->class->getErrors());
  }

  #[Test]
  public function radioOptions_with_invalid_value(): void
  {
    $result = $this->class->call('radioOptions', 'gender', 'man', ['male', 'female', 'others']);
    $this->assertFalse($result);
    $this->assertArrayHasKey('gender', $this->class->getErrors());
    $this->assertSame('有効な選択肢を選んでください', $this->class->getErrors()['gender']);
  }

  #[Test]
  public function maxLength_with_valid_Value(): void
  {
    $max = 10;
    $value = str_repeat('a', 10);
    $result = $this->class->call('maxLength', 'name', $value, $max);
    $this->assertTrue($result);
    $this->assertSame($value, $this->class->getOld()['name']);
    $this->assertArrayNotHasKey('name', $this->class->getErrors());
  }

  #[Test]
  public function maxLength_with_invalid_Value(): void
  {
    $max = 10;
    $value = str_repeat('a', 11);
    $result = $this->class->call('maxLength', 'name', $value, $max);
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->class->getErrors());
    $this->assertSame("{$max}文字以内で入力願います", $this->class->getErrors()['name']);
  }

  #[Test]
  public function minLength_with_valid_Value(): void
  {
    $min = 3;
    $value = str_repeat('a', 3);
    $result = $this->class->call('minLength', 'name', $value, $min);
    $this->assertTrue($result);
    $this->assertSame($value, $this->class->getOld()['name']);
    $this->assertArrayNotHasKey('name', $this->class->getErrors());
  }

  #[Test]
  public function minLength_with_invalid_Value(): void
  {
    $min = 3;
    $value = str_repeat('a', 2);
    $result = $this->class->call('minLength', 'name', $value, $min);
    $this->assertFalse($result);
    $this->assertArrayHasKey('name', $this->class->getErrors());
    $this->assertSame("{$min}文字以上で入力願います", $this->class->getErrors()['name']);
  }

  #[Test]
  public function numeric_with_valid_value(): void
  {
    $result = $this->class->call('numeric', 'number', '123456');
    $this->assertTrue($result);
    $this->assertArrayHasKey('number', $this->class->getOld());
    $this->assertSame('123456', $this->class->getOld()['number']);
  }

  #[Test]
  public function numeric_with_invalid_value(): void
  {
    $result = $this->class->call('numeric', 'number', '123-456');
    $this->assertFalse($result);
    $this->assertArrayHasKey('number', $this->class->getErrors());
    $this->assertSame('数値で入力願います', $this->class->getErrors()['number']);
  }

  #[Test]
  public function integer_with_valid_value(): void
  {
    $result = $this->class->call('integer', 'price', 10000);
    $this->assertTrue($result);
    $this->assertArrayHasKey('price', $this->class->getOld());
    $this->assertSame(10000, $this->class->getOld()['price']);
  }

  #[Test]
  public function numeric_with_0_value(): void
  {
    $result = $this->class->call('integer', 'price', 0);
    $this->assertFalse($result);
    $this->assertArrayHasKey('price', $this->class->getErrors());
    $this->assertSame('1～9999の範囲の整数で入力願います', $this->class->getErrors()['price']);
  }

  #[Test]
  public function numeric_with_value_over_max(): void
  {
    $result = $this->class->call('integer', 'price', 10001);
    $this->assertFalse($result);
    $this->assertArrayHasKey('price', $this->class->getErrors());
    $this->assertSame('1～9999の範囲の整数で入力願います', $this->class->getErrors()['price']);
  }

  #[Test]
  public function email_with_valid_value(): void
  {
    $result = $this->class->call('email', 'email', 'test@mail.com');
    $this->assertTrue($result);
    $this->assertArrayHasKey('email', $this->class->getOld());
    $this->assertSame('test@mail.com', $this->class->getOld()['email']);
  }

  #[Test]
  public function email_with_invalid_value(): void
  {
    $result = $this->class->call('email', 'email', 'testmail.com');
    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->class->getErrors());
    $this->assertSame('メールアドレスの形式が正しくありません', $this->class->getErrors()['email']);
  }

  #[Test]
  public function phoneNumber_with_valid_value(): void
  {
    $result = $this->class->call('phoneNumber', 'phone', '012-345-6789');
    $this->assertTrue($result);
    $this->assertArrayHasKey('phone', $this->class->getOld());
    $this->assertSame('012-345-6789', $this->class->getOld()['phone']);
  }

  #[Test]
  public function phoneNumber_with_invalid_value(): void
  {
    $result = $this->class->call('phoneNumber', 'phone', '012-345-678912');
    $this->assertFalse($result);
    $this->assertArrayHasKey('phone', $this->class->getErrors());
    $this->assertSame('電話番号の形式が正しくありません', $this->class->getErrors()['phone']);

    $result = $this->class->call('phoneNumber', 'phone', '012-345-678');
    $this->assertFalse($result);
    $this->assertArrayHasKey('phone', $this->class->getErrors());
    $this->assertSame('電話番号の形式が正しくありません', $this->class->getErrors()['phone']);
  }

  #[Test]
  public function gender_with_valid_value(): void
  {
    $result = $this->class->call('gender', 'gender', 'female');
    $this->assertTrue($result);
    $this->assertArrayHasKey('gender', $this->class->getOld());
    $this->assertSame('female', $this->class->getOld()['gender']);
  }

  #[Test]
  public function gender_with_invalid_value(): void
  {
    $result = $this->class->call('gender', 'gender', 'abcd');
    $this->assertFalse($result);
    $this->assertArrayHasKey('gender', $this->class->getErrors());
    $this->assertSame('選択が正しくありません', $this->class->getErrors()['gender']);
  }

  #[Test]
  #[DataProvider('validPasswordProvider')]
  public function password_with_vaid_value($password): void
  {
    $result = $this->class->call('password', 'password', $password);
    $this->assertTrue($result);
  }
  
  #[Test]
  #[DataProvider('invalidPasswordProvider')]
  public function password_with_invaid_value($password): void
  {
    $result = $this->class->call('password', 'password', $password);
    $this->assertFalse($result);
  }
  
  #[Test]
  public function confirmed_with_valid_value(): void
  {
    $result = $this->class->call('confirmed', 'password', 'password123', 'password123');
    $this->assertTrue($result);
  }

  #[Test]
  public function confirmed_with_invalid_value(): void
  {
    $result = $this->class->call('confirmed', 'password', 'password123', 'password456');
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->class->getErrors());
    $this->assertSame('確認用と一致していません、再入力願います', $this->class->getErrors()['password']);
  }

  #[Test]
  public function validBoolean_with_valid_value()
  {
    $result = $this->class->call('validBoolean', 'test', 0);
    $this->assertTrue($result);
    $this->assertArrayHasKey('test', $this->class->getOld());
    $this->assertSame(0, $this->class->getOld()['test']);
  }

  #[Test]
  public function validBoolean_with_invalid_value()
  {
    $result = $this->class->call('validBoolean', 'test', 2);
    $this->assertFalse($result);
    $this->assertArrayHasKey('test', $this->class->getErrors());
    $this->assertSame('無効な値です', $this->class->getErrors()['test']);
  }

  #[Test]
  public function dateForm_with_valid_value()
  {
    $result = $this->class->call('dateForm', 'test', '2025-10-13');

    $this->assertTrue($result);
    $this->assertArrayHasKey('test', $this->class->getOld());
    $this->assertSame('2025-10-13', $this->class->getOld()['test']);
  }

  #[Test]
  #[DataProvider('invalidDateProvider')]
  public function dateForm_with_invalid_value($date)
  {
    $result = $this->class->call('dateForm', 'test', $date);
    $this->assertFalse($result);
    $this->assertArrayHasKey('test', $this->class->getErrors());
    $this->assertSame('日付形式が異なっています', $this->class->getErrors()['test']);
  }

  #[Test]
  #[DataProvider('invalidTimeProvider')]
  public function timeForm_with_invalid_value($time)
  {
    $result = $this->class->call('timeForm', 'test', $time);
    $this->assertFalse($result);
    $this->assertArrayHasKey('test', $this->class->getErrors());
    $this->assertSame('時刻形式が異なっています', $this->class->getErrors()['test']);
  }

  public static function validPasswordProvider(): array
  {
    return [
      ['abc12345'],
      ['pass5678'],
      ['X9Y8Z7W6'],
      ['Test0000'],
    ];
  }

  public static function invalidPasswordProvider(): array
  {
    return [
      ['short'],
      ['12345678'],
      ['password'],
      ['あいうえお123'],
      ['abc12345678910'],
      [''],
    ];
  }

  public static function invalidDateProvider(): array
  {
    return [
      ['2025-2-13'],
      ['2025-13-01'],
      ['2025-02-30'],
      ['2025/02/10']
    ];
  }
  public static function invalidTimeProvider(): array
  {
    return [
      ['12:1'],
      ['23:60'],
      ['25:00'],
      ['1:00']
    ];
  }

}