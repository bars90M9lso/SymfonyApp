<?php

namespace App\Service;

use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;
use App\Service\UserManager;

final class ResetPasswordService
{
    public function __construct(private ResetPasswordHelperInterface $resetPasswordHelper, private UserManager $userManager, private NotificationMailer $notificationMailer) 
    {
    }

    public function generateAndSendResetToken(string $email)
    {
        $user = $this->userManager->findByEmail($email);

        if (!$user) { return null; }

        try 
        {
            $resetToken = $this->resetPasswordHelper->generateResetToken($user);
        
        } catch (\Throwable $e) { return null; }

        $this->notificationMailer->sendResetPassword((string) $user->getEmail(), $resetToken);

        return $resetToken;
    }
}
