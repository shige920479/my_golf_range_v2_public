<?php

use App\Services\Security\PasswordService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PasswordServiceTest extends TestCase
{
    private PasswordService $service;

    protected function setUp(): void
    {
        $this->service = new PasswordService();
    }

    #[Test]
    public function hashPassword_creates_valid_hash(): void
    {
        $plain = 'secret123';

        $hashed = $this->service->hashPassword($plain);

        $this->assertNotSame($plain, $hashed);
        $this->assertTrue(password_verify($plain, $hashed));
    }

    #[Test]
    public function verifyPassword_returns_true_for_correct_password(): void
    {
        $plain = 'mypassword';
        $hashed = password_hash($plain, PASSWORD_BCRYPT);

        $result = $this->service->verifyPassword($plain, $hashed);

        $this->assertTrue($result);
    }

    #[Test]
    public function verifyPassword_returns_false_for_wrong_password(): void
    {
        $plain = 'mypassword';
        $hashed = password_hash($plain, PASSWORD_BCRYPT);

        $result = $this->service->verifyPassword('wrong', $hashed);

        $this->assertFalse($result);
    }
}