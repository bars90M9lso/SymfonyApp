<?php

namespace App\Tests\Service;

use PHPUnit\Framework\TestCase;
use App\Service\ResetPasswordService;
use App\Service\UserManager;
use App\Service\NotificationMailer;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

class ResetPasswordServiceTest extends TestCase
{
    public function testGenerateAndSendResetTokenReturnsNullWhenUserNotFound(): void
    {
        $userManager = $this->createMock(UserManager::class);
        $userManager->method('findByEmail')->willReturn(null);

        $notificationMailer = $this->createMock(NotificationMailer::class);
        $resetHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);

        $service = new ResetPasswordService($resetHelper, $entityManager, $userManager, $notificationMailer);

        $this->assertNull($service->generateAndSendResetToken('no@ex.com'));
    }

    public function testGenerateAndSendResetTokenSendsEmailAndReturnsToken(): void
    {
        $user = new \App\Entity\User();
        $user->setEmail('ok@ex.com');
        $user->setUsername('u');

        $userManager = $this->createMock(UserManager::class);
        $userManager->method('findByEmail')->willReturn($user);

        $resetToken = new \SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken(
            'selector_verifier_token',
            new \DateTimeImmutable('+1 hour'),
            (int) time()
        );

        $resetHelper = $this->createMock(ResetPasswordHelperInterface::class);
        $resetHelper->expects($this->once())->method('generateResetToken')->with($user)->willReturn($resetToken);

        $notificationMailer = $this->createMock(NotificationMailer::class);
        $notificationMailer->expects($this->once())->method('sendResetPassword')->with('ok@ex.com', $resetToken);

        $entityManager = $this->createMock(\Doctrine\ORM\EntityManagerInterface::class);

        $service = new ResetPasswordService($resetHelper, $entityManager, $userManager, $notificationMailer);
        $res = $service->generateAndSendResetToken('ok@ex.com');
        $this->assertSame($resetToken, $res);
    }
}
