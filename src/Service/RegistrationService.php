<?php

namespace App\Service;

use App\Entity\User;
use App\Service\UserManager;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

final class RegistrationService
{
    private const VERIFICATION_CODE_TTL = 600;
    private const MAX_VERIFICATION_ATTEMPTS = 5;

    public function __construct(
        private NotificationMailer $notificationMailer,
        private UserManager $userManager
    ) {
    }

    public function startRegistration(string $username, string $email, string $passwordHash, SessionInterface $session): void
    {
        $code = (string) random_int(100000, 999999);

        $session->set('pending_registration', [
            'username' => $username,
            'email' => $email,
            'passwordHash' => $passwordHash,
            'code_hash' => hash('sha256', $code),
            'expires_at' => time() + self::VERIFICATION_CODE_TTL,
            'attempts' => 0,
        ]);

        $this->notificationMailer->sendVerificationCode($email, $code, (int) ceil(self::VERIFICATION_CODE_TTL / 60));
    }

    public function confirmRegistration(string $inputCode, SessionInterface $session): User
    {
        $pending = $session->get('pending_registration');

        if (!$pending) {
            throw new \RuntimeException('Нет данных для подтверждения регистрации.');
        }

        if ($pending['expires_at'] < time()) {
            $session->remove('pending_registration');
            throw new \RuntimeException('Время подтверждения регистрации истекло.');
        }

        if (!hash_equals($pending['code_hash'], hash('sha256', $inputCode))) {
            $pending['attempts'] = ($pending['attempts'] ?? 0) + 1;

            if ($pending['attempts'] >= self::MAX_VERIFICATION_ATTEMPTS) {
                $session->remove('pending_registration');
                throw new \RuntimeException('Превышено количество попыток ввода кода.');
            }

            $session->set('pending_registration', $pending);
            throw new \RuntimeException('Неверный код подтверждения.');
        }

        $user = new User();
        $user->setUsername($pending['username']);
        $user->setEmail($pending['email']);
        $user->setPassword($pending['passwordHash']);

        $this->userManager->createUser($pending['username'], $pending['email'], $pending['passwordHash']);

        $session->remove('pending_registration');

        return $user;
    }
}
