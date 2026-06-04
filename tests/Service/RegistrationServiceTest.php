<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\RegistrationService;
use App\Service\NotificationMailer;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;

class RegistrationServiceTest extends TestCase
{
    public function testStartRegistrationSetsSessionAndSendsEmail(): void
    {
        $email = 'test@example.com';

        $notificationMailer = $this->createMock(NotificationMailer::class);
        $notificationMailer->expects($this->once())
            ->method('sendVerificationCode')
            ->with(
                $this->equalTo($email),
                $this->callback(function ($code) {
                    return is_string($code) && preg_match('/^\d{6}$/', $code);
                }),
                $this->callback(function ($v) { return is_int($v); })
            );

        $userManager = $this->createMock(UserManager::class);

        $service = new RegistrationService($notificationMailer, $userManager);

        $session = new Session(new MockArraySessionStorage());
        $service->startRegistration('user1', $email, 'hash', $session);

        $pending = $session->get('pending_registration');
        $this->assertIsArray($pending);
        $this->assertSame('user1', $pending['username']);
        $this->assertSame($email, $pending['email']);
        $this->assertSame('hash', $pending['passwordHash']);
        $this->assertMatchesRegularExpression('/^[0-9a-f]{64}$/', $pending['code_hash']);
        $this->assertSame(0, $pending['attempts']);
        $this->assertGreaterThan(time() - 5, $pending['expires_at']);
    }

    public function testConfirmRegistrationSuccessCreatesUserAndRemovesSession(): void
    {
        $notificationMailer = $this->createMock(NotificationMailer::class);

        $userManager = $this->createMock(UserManager::class);
        $userManager->expects($this->once())
            ->method('createUser')
            ->with('user1', 'test@example.com', 'hash')
            ->willReturnCallback(function ($a, $b, $c) {
                $u = new \App\Entity\User();
                $u->setUsername($a);
                $u->setEmail($b);
                $u->setPassword($c);
                return $u;
            });

        $service = new RegistrationService($notificationMailer, $userManager);

        $session = new Session(new MockArraySessionStorage());
        $code = '123456';
        $session->set('pending_registration', [
            'username' => 'user1',
            'email' => 'test@example.com',
            'passwordHash' => 'hash',
            'code_hash' => hash('sha256', $code),
            'expires_at' => time() + 600,
            'attempts' => 0,
        ]);

        $user = $service->confirmRegistration($code, $session);

        $this->assertInstanceOf(\App\Entity\User::class, $user);
        $this->assertNull($session->get('pending_registration'));
    }
}
