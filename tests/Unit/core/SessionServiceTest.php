<?php

use App\Services\Core\SessionService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class SessionServiceTest extends TestCase
{
  private SessionService $session;

  protected function setUp(): void
  {
    if (session_status() !== PHP_SESSION_ACTIVE) session_start();
    $_SESSION = [];
    $this->session = new SessionService();
  }
  
  #[Test]
  public function it_can_set_and_get_session_values(): void
  {
    $this->session->set('user_id', 12345);

    $this->assertArrayHasKey('user_id', $_SESSION);
    $this->assertSame(12345, $_SESSION['user_id']);
    $this->assertSame(12345, $this->session->get('user_id'));
  }

  #[Test]
  public function it_can_set_and_get_session_nest_values(): void
  {
    $this->session->set('nest1.nest2.nest3', 'nest123');

    $this->assertArrayHasKey('nest1', $_SESSION);
    $this->assertArrayHasKey('nest2', $_SESSION['nest1']);
    $this->assertArrayHasKey('nest3', $_SESSION['nest1']['nest2']);
    $this->assertSame('nest123', $_SESSION['nest1']['nest2']['nest3']);
    $this->assertSame('nest123', $this->session->get('nest1.nest2.nest3'));
  }

  #[Test]
  public function it_can_set_and_get_session_array_values(): void
  {
    $this->session->set('user', [
      'id' => 5,
      'name' => 'user123',
      'email' => 'user@mail.com'
    ]);

    $this->assertArrayHasKey('id', $_SESSION['user']);
    $this->assertArrayHasKey('name', $_SESSION['user']);
    $this->assertArrayHasKey('email', $_SESSION['user']);
    $this->assertSame([
        'id' => 5,
        'name' => 'user123',
        'email' => 'user@mail.com'
      ], $this->session->get('user'));
  }

  #[Test]
  public function it_can_forget_session_values(): void
  {
    $_SESSION = ['foo' => 'bar'];

    $this->session->forget('foo');

    $this->assertNull($this->session->get('foo'));
  }
  #[Test]
  public function it_can_forget_session_nest_values(): void
  {
    $_SESSION['nest1']['nest2']['nest3'] = 'nest123';

    $this->session->forget('nest1.nest2.nest3');

    $this->assertNull($this->session->get('nest1.nest2.nest3'));
  }
  
  #[Test]
  public function it_returns_default_when_key_not_found(): void
  {
    $this->assertNull($this->session->get('not_exist'));
    $this->assertSame('default', $this->session->get('not_exist', 'default'));
  }

  #[Test]
  public function it_can_flash_and_remove(): void
  {
    $_SESSION['notice'] = 'ログインしました';
    $flash = $this->session->flash('notice');

    $this->assertSame('ログインしました', $flash);
    $this->assertNull($this->session->get('notice'));
  }

  #[Test]
  public function it_overwirtes_nested_array(): void
  {
    $this->session->set('user.id', 1);
    $this->session->set('user.name', 'taro');

    $this->assertSame([
      'id' => 1, 
      'name' => 'taro'
    ], $this->session->get('user'));
  }

  #[Test]
  public function forgetMulti_can_forget(): void
  {
    $this->session->set('user.id', 1);
    $this->session->set('user.name', 'taro');
    $this->session->set('token', 'token123');
    
    $this->session->forgetMulti(['user.id', 'user.name', 'token']);
    $this->assertSame([], $this->session->get('user'));
    $this->assertNull($this->session->get('user.id'));
    $this->assertNull($this->session->get('user.name'));
    $this->assertNull($this->session->get('token'));
  }

  #[Test]
  public function loginSessionGenerate_sets_correct_user_session_for_user_role(): void
  {
    $user = [
        'id' => 1,
        'lastname' => '山田',
        'firstname' => '太郎',
        'email' => 'yamada@mail.com'
    ];

    $this->session->loginSessionGenerate($user, 'user');

    $this->assertSame(1, $_SESSION['user']['id']);
    $this->assertSame('山田太郎', $_SESSION['user']['name']);
    $this->assertSame('yamada@mail.com', $_SESSION['user']['email']);
  }

  #[Test]
  public function loginSessionGenerate_sets_correct_admin_session_for_admin_role(): void
  {
    $user = [
        'id' => 99,
        'name' => '管理者A',
        'email' => 'admin@mail.com'
    ];

    $this->session->loginSessionGenerate($user, 'admin');

    $this->assertSame(99, $_SESSION['admin']['id']);
    $this->assertSame('管理者A', $_SESSION['admin']['name']);
    $this->assertSame('admin@mail.com', $_SESSION['admin']['email']);
  }
}