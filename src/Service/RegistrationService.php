<?php

namespace App\Service;

use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RegistrationService
{
    private const VERIFICATION_CODE_TTL = 600;
    private const MAX_VERIFICATION_ATTEMPTS = 5;
    private const SESSION_KEY = 'pending_registration';

    private function sendNewCodeOnly(array $pending, SessionInterface $session): void
    {
        $code = $this->generateCode();
        $pending['code_hash'] = hash('sha256', $code);
        $pending['expires_at'] = time() + self::VERIFICATION_CODE_TTL;
        $pending['resend_available_at'] = time() + 60;
        $pending['attempts'] = 0;
        unset($pending['locked']);
        $session->set(self::SESSION_KEY, $pending);

        $this->sendCode($pending['email'], $code);
    }

    private function getPendingRegistration(SessionInterface $session): array
    {
        $pending = $session->get(self::SESSION_KEY);

        if (!$pending) 
        {
            throw new \RuntimeException('notifications.error.no_data_for_confirmation');
        }

        return $pending;
    }

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function sendCode(string $email, string $code): void
    {
        $this->notificationMailer->sendVerificationCode($email, $code, (int) ceil(self::VERIFICATION_CODE_TTL / 60));
    }

    public function __construct(private NotificationMailer $notificationMailer, private UserManager $userManager)
    {
    }

    public function startRegistration(string $username, string $email, string $passwordHash, SessionInterface $session): void
    {
        $code = $this->generateCode();

        $session->set(self::SESSION_KEY, 
        [
            'username' => $username,
            'email' => $email,
            'passwordHash' => $passwordHash,
            'code_hash' => hash('sha256', $code),
            'expires_at' => time() + self::VERIFICATION_CODE_TTL,
            'attempts' => 0,
            'resend_available_at' => time() + 60,
        ]);

        $this->sendCode($email, $code);
    }

    public function resendCode(SessionInterface $session): void
    {
        $pending = $this->getPendingRegistration($session);

        $this->sendNewCodeOnly($pending, $session);
    }

    public function confirmRegistration(string $inputCode, SessionInterface $session): void
    {   
        $pending = $this->getPendingRegistration($session);

        if (!empty($pending['locked']))
        { 
            throw new \RuntimeException('notifications.error.too_many_attempts');
        }
        
        if ($pending['expires_at'] < time()) 
        {
            $session->remove(self::SESSION_KEY);
            throw new \RuntimeException('notifications.error.expired_verification');
        }

        if (!hash_equals($pending['code_hash'], hash('sha256', $inputCode))) 
        {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            
            if ($pending['attempts'] >= self::MAX_VERIFICATION_ATTEMPTS) 
            {
                $pending['locked'] = true;
                $session->set(self::SESSION_KEY, $pending);

                throw new \RuntimeException('notifications.error.too_many_attempts');
            }

            $session->set(self::SESSION_KEY, $pending);

            throw new \RuntimeException('notifications.error.invalid_code');
        }

        $this->userManager->createUser($pending['username'], $pending['email'], $pending['passwordHash']);
        $session->remove(self::SESSION_KEY);
    }

}
