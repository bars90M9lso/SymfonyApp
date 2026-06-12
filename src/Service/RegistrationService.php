<?php

namespace App\Service;

use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RegistrationService
{
    private const VERIFICATION_CODE_TTL = 600;
    private const MAX_VERIFICATION_ATTEMPTS = 5;

    public function __construct(private NotificationMailer $notificationMailer, private UserManager $userManager)
    {
    }

    public function startRegistration(string $username, string $email, string $passwordHash, SessionInterface $session): void
    {
        $this->generateAndSendCode($username, $email, $passwordHash,$session);
    }

    private function generateAndSendCode(string $username, string $email, string $passwordHash, SessionInterface $session): void
    {
        $code = $this->generateCode();

        $session->set('pending_registration', 
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
        $pending = $session->get('pending_registration');

        if (!$pending) {
            throw new \RuntimeException('Нет данных для повторной отправки кода.');
            redirectToRoute('registration');    
        }

        if (empty($pending['locked'])) {
            if (($pending['resend_available_at'] ?? 0) > time()) {
                $seconds = $pending['resend_available_at'] - time();
                throw new \RuntimeException("Подождите {$seconds} сек.");
            }
        }

        $this->sendNewCodeOnly($pending, $session);
    }

    private function sendNewCodeOnly(array $pending, SessionInterface $session): void
    {
        $code = $this->generateCode();

        $pending['code_hash'] = hash('sha256', $code);
        $pending['expires_at'] = time() + self::VERIFICATION_CODE_TTL;
        $pending['resend_available_at'] = time() + 60;

        $pending['attempts'] = 0;
        unset($pending['locked']);

        $session->set('pending_registration', $pending);

        $this->sendCode($pending['email'], $code);
    }

    public function confirmRegistration(string $inputCode, SessionInterface $session): void
    {   
        $pending = $session->get('pending_registration');

        if (!$pending) {
            throw new \RuntimeException('Нет данных для подтверждения регистрации.');
            redirectToRoute('registration');    
        }

        if (!empty($pending['locked'])) {
            throw new \RuntimeException('Превышено количество попыток ввода кода.');
        }

        if ($pending['expires_at'] < time()) {
            $session->remove('pending_registration');
            throw new \RuntimeException('Время подтверждения регистрации истекло.');
        }

        if (!hash_equals($pending['code_hash'], hash('sha256', $inputCode))) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;
            
            if ($pending['attempts'] >= self::MAX_VERIFICATION_ATTEMPTS - 1) {
                $pending['locked'] = true;
                $session->set('pending_registration', $pending);

                throw new \RuntimeException('Превышено количество попыток ввода кода.');
            }

            $session->set('pending_registration', $pending);

            throw new \RuntimeException('Неверный код подтверждения.');
        }

        $this->userManager->createUser($pending['username'], $pending['email'], $pending['passwordHash']);
        $session->remove('pending_registration');
    }

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function sendCode(string $email, string $code): void
    {
        $this->notificationMailer->sendVerificationCode($email, $code, (int) ceil(self::VERIFICATION_CODE_TTL / 60));
    }
}
