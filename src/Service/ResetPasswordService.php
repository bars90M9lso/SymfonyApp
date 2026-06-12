<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use App\Service\UserManager;

final class ResetPasswordService
{
    public function __construct(private ResetPasswordHelperInterface $resetPasswordHelper, private EntityManagerInterface $entityManager, private UserManager $userManager, private NotificationMailer $notificationMailer) 
    {
    }

    public function generateAndSendResetToken(string $email)
    {
        $user = $this->userManager->findByEmail($email);

        if (!$user) {
            return null;
        }

        try {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        } catch (\Throwable $e) {
            return null;
        }

        $this->notificationMailer->sendResetPassword((string) $user->getEmail(), $resetToken);

        return $resetToken;
    }
}
