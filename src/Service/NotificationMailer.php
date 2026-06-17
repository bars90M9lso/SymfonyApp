<?php

namespace App\Service;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\Translation\TranslatorInterface;

class NotificationMailer
{
    public function __construct(
        private MailerInterface $mailer,
        private TranslatorInterface $translator,
        #[Autowire('%env(MAILER_FROM_EMAIL)%')]
        private string $fromEmail,
        #[Autowire('%env(MAILER_FROM_NAME)%')]
        private string $fromName,
    ) { }

    public function sendVerificationCode(string $toEmail, string $code, int $ttlMinutes = 10): void
    {
        $locale = "ru";
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($toEmail)
            ->subject($this->translator->trans('registration.email_message.subject', [], null, $locale))
            ->htmlTemplate('Authentication/Registration/email.html.twig')
            ->context(
            [
                'code' => $code,
                'ttlMinutes' => $ttlMinutes,
                'locale' => $locale,
            ]);

        $this->mailer->send($email);
    }

    public function sendResetPassword(string $toEmail, $resetToken): void
    {
        $locale = "ru";
        $email = (new TemplatedEmail())
            ->from(new Address($this->fromEmail, $this->fromName))
            ->to($toEmail)
            ->subject($this->translator->trans('reset_password.email_message.subject', [], null, $locale))
            ->htmlTemplate('Authentication/ResetPassword/email.html.twig')
            ->context(
            [
                'resetToken' => $resetToken,
                'locale' => $locale,
            ]);

        $this->mailer->send($email);
    }
}
