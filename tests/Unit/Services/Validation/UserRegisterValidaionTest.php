<?php

use App\Services\Validation\UserRegisterValidaion;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class UserRegisterValidaionTest extends TestCase
{
  private UserRegisterValidaion $validator;
  private array $request;

  protected function setUp(): void
  {
    $this->validator = new UserRegisterValidaion();
    $this->request = [
      'firstname' => '太郎',
      'lastname' => '山田',
      'firstnamekana' => 'たろう',
      'lastnamekana' => 'やまだ',
      'email' => 'validationtest@mail.com',
      'phone' => '012-3456-7890',
      'gender' => 'male',
      'password' => 'password123',
      'password_confirmation' => 'password123'
    ];
  }

  #[Test]
  public function tempInputValidate_with_valid_value(): void
  {
    $req['email'] = 'test@email.com';
    $req['email_confirmation'] = 'test@email.com';
    $result = $this->validator->tempInputValidate($req);
    
    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
    $this->assertArrayHasKey('email', $this->validator->getOld());
    $this->assertArrayHasKey('email_confirmation', $this->validator->getOld());
  }

  #[Test]
  public function tempInputValidate_with_empty_email(): void
  {
    $req['email'] = '';
    $req['email_confirmation'] = 'test@email.com';
    $result = $this->validator->tempInputValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayNotHasKey('email', $this->validator->getOld());
    $this->assertArrayHasKey('email_confirmation', $this->validator->getOld());
    $this->assertArrayHasKey('email', $this->validator->getErrors());
    $this->assertArrayNotHasKey('email_confirmation', $this->validator->getErrors());
  }

  #[Test]
  public function tempInputValidate_with_empty_email_confirmation(): void
  {
    $req['email'] = 'test@email.com';
    $req['email_confirmation'] = '';
    $result = $this->validator->tempInputValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->validator->getOld());
    $this->assertArrayHasKey('email', $this->validator->getErrors());
    $this->assertSame('確認用と一致していません、再入力願います', $this->validator->getErrors()['email']);
    $this->assertArrayNotHasKey('email_confirmation', $this->validator->getOld());
    $this->assertArrayHasKey('email_confirmation', $this->validator->getErrors());
    $this->assertSame('必須事項です、入力願います', $this->validator->getErrors()['email_confirmation']);
  }

  #[Test]
  public function tempInputValidate_with_invalid_email(): void
  {
    $req['email'] = 'test.com';
    $req['email_confirmation'] = 'test@email.com';
    $result = $this->validator->tempInputValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('email', $this->validator->getErrors());
    $this->assertSame('メールアドレスの形式が正しくありません', $this->validator->getErrors()['email']);
    $this->assertArrayHasKey('email_confirmation', $this->validator->getOld());
    $this->assertArrayNotHasKey('email_confirmation', $this->validator->getErrors());
  }

  #[Test]
  public function registerValidate_with_valid_value(): void
  {
    $req = $this->request;

    $result = $this->validator->registerValidate($req);
    
    $this->assertTrue($result);
    $this->assertSame([], $this->validator->getErrors());
    $this->assertArrayHasKey('firstname', $this->validator->getOld());
    $this->assertArrayHasKey('lastname', $this->validator->getOld());
    $this->assertArrayHasKey('firstnamekana', $this->validator->getOld());
    $this->assertArrayHasKey('lastnamekana', $this->validator->getOld());
    $this->assertArrayHasKey('phone', $this->validator->getOld());
    $this->assertArrayHasKey('gender', $this->validator->getOld());
    $this->assertArrayNotHasKey('password', $this->validator->getOld());
    $this->assertArrayNotHasKey('password_confirmation', $this->validator->getOld());
  }
  #[Test]
  public function registerValidate_with_empty_firstname(): void
  {
    $req = $this->request;
    $req['firstname'] = '';

    $result = $this->validator->registerValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('firstname', $this->validator->getErrors());
    $this->assertSame('必須事項です、入力願います', $this->validator->getErrors()['firstname']);
    $this->assertArrayHasKey('lastname', $this->validator->getOld());
    $this->assertArrayHasKey('firstnamekana', $this->validator->getOld());
    $this->assertArrayHasKey('lastnamekana', $this->validator->getOld());
    $this->assertArrayHasKey('phone', $this->validator->getOld());
    $this->assertArrayHasKey('gender', $this->validator->getOld());
    $this->assertArrayNotHasKey('password', $this->validator->getOld());
    $this->assertArrayNotHasKey('password_confirmation', $this->validator->getOld());
  }

  #[Test]
  public function registerValidate_with_over_maxlength_firstname(): void
  {
    $req = $this->request;
    $req['firstname'] = str_repeat('a', 51);

    $result = $this->validator->registerValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('firstname', $this->validator->getErrors());
    $this->assertSame('50文字以内で入力願います', $this->validator->getErrors()['firstname']);
    $this->assertArrayHasKey('lastname', $this->validator->getOld());
    $this->assertArrayHasKey('firstnamekana', $this->validator->getOld());
    $this->assertArrayHasKey('lastnamekana', $this->validator->getOld());
    $this->assertArrayHasKey('phone', $this->validator->getOld());
    $this->assertArrayHasKey('gender', $this->validator->getOld());
  }

  #[Test]
  public function registerValidate_with_empty_password(): void
  {
    $req = $this->request;
    $req['password'] = '';
    $result = $this->validator->registerValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertArrayNotHasKey('password', $this->validator->getOld());
    $this->assertArrayNotHasKey('password_confirmation', $this->validator->getOld());
    $this->assertArrayNotHasKey('password_confirmation', $this->validator->getErrors());
  }

  #[Test]
  public function registerValidate_with_empty_password_confirmation(): void
  {
    $req = $this->request;
    $req['password_confirmation'] = '';
    $result = $this->validator->registerValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertSame('確認用と一致していません、再入力願います', $this->validator->getErrors()['password']);
    $this->assertArrayHasKey('password_confirmation', $this->validator->getErrors());
    $this->assertSame('必須事項です、入力願います', $this->validator->getErrors()['password_confirmation']);
  }

  #[Test]
  public function registerValidate_with_invalid_password(): void
  {
    $req = $this->request;
    $req['password'] = '123';
    $result = $this->validator->registerValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertSame('8～12文字の半角英数字で入力願います', $this->validator->getErrors()['password']);
    $this->assertArrayNotHasKey('password_confirmation', $this->validator->getErrors());
  }

  #[Test]
  public function registerValidate_with_mismatch_password(): void
  {
    $req = $this->request;
    $req['password_confirmation'] = 'mismatch123';
    $result = $this->validator->registerValidate($req);
    
    $this->assertFalse($result);
    $this->assertArrayHasKey('password', $this->validator->getErrors());
    $this->assertSame('確認用と一致していません、再入力願います', $this->validator->getErrors()['password']);
    $this->assertArrayNotHasKey('password_confirmation', $this->validator->getErrors());
  }

  #[Test]
  public function registerValidate_with_null_gender(): void
  {
    $req = $this->request;
    $req['gender'] = '';

    $result = $this->validator->registerValidate($req);
    
    $this->assertTrue($result);
  }
}