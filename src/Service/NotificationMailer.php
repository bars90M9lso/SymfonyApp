<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class NotificationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        #[Autowire('%env(MAILER_FROM_EMAIL)%')]
        private string $fromEmail,
        #[Autowire('%env(MAILER_FROM_NAME)%')]
        private string $fromName,
    ) {
    }

    public function sendVerificationCode(string $toEmail, string $code, int $ttlMinutes = 10): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($toEmail)
            ->subject('Код подтверждения регистрации')
            ->htmlTemplate('Authentication/Registration/email.html.twig')
            ->context([
                'code' => $code,
                'ttlMinutes' => $ttlMinutes,
            ]);

        $this->mailer->send($email);
    }

    public function sendResetPassword(string $toEmail, $resetToken): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($toEmail)
            ->subject('Запрос на сброс пароля')
            ->htmlTemplate('Authentication/ResetPassword/email.html.twig')
            ->context([
                'resetToken' => $resetToken,
            ]);

        $this->mailer->send($email);
    }
}
