<?php

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\EmailVerificationCodeFormType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;

class RegistrationController extends AbstractController
{
    public function __construct(
        #[Autowire('%env(MAILER_FROM_EMAIL)%')]
        private string $mailerFromEmail,
        #[Autowire('%env(MAILER_FROM_NAME)%')]
        private string $mailerFromName
    ) {
    }

    #[Route('/registration', name: 'registration')]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entlMng, MailerInterface $mailer): Response 
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $existingUser = $entlMng->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);
            $existingEmail = $entlMng->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            
            if ($existingUser)             {
                $form->addError(new FormError('Пользователь с таким логином уже зарегистрирован'));
                return $this->render('Authentication/Registration/registration.html.twig', ['registrationForm' => $form,]);
            }
            if ($existingEmail)             {
                $form->addError(new FormError('Пользователь с таким email уже зарегистрирован'));
                return $this->render('Authentication/Registration/registration.html.twig', ['registrationForm' => $form,]);
            }

            $passwordHash = $passwordHasher->hashPassword($user, (string) $form->get('password')->getData(),);
            $code = (string) random_int(100000, 999999);
            
            $request->getSession()->set('pending_registration', [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'passwordHash' => $passwordHash,
                'code_hash' => hash('sha256', $code),
                'expires_at' => time() + 600,
            ]);

            $this->sendVerificationEmail($user->getEmail(), $code, $mailer);
            return $this->redirectToRoute('registration_verify');
        }

        return $this->render('Authentication/Registration/registration.html.twig', [
            'registrationForm' => $form,
        ]);
        
    }

    #[Route('/registration/verify', name: 'registration_verify')]
    public function verify(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entlMng): Response
    {
        $session = $request->getSession();
        $pending = $session->get('pending_registration');

        if (!$pending) {
            $this->addFlash('error', 'Нет данных для подтверждения регистрации. Пожалуйста, зарегистрируйтесь заново.');
            return $this->redirectToRoute('registration');
        }

        $form = $this->createForm(EmailVerificationCodeFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            if ($pending['expires_at'] < time()) 
            {
                $session->remove('pending_registration');
                $this->addFlash('error', 'Время для подтверждения регистрации истекло. Пожалуйста, зарегистрируйтесь заново.');
                return $this->redirectToRoute('registration');
            }
            
            $inputCode = $form->get('code')->getData();
            if (hash('sha256', $inputCode) !== $pending['code_hash']) 
            {
                $this->addFlash('error', 'Неверный код подтверждения. Пожалуйста, попробуйте снова.');
                return $this->redirectToRoute('registration_verify');
            }

            $user = new User();
            $user->setUsername($pending['username']);
  
            $user->setEmail($pending['email']);
            $user->setPassword($pending['passwordHash']);
            
            $entlMng->persist($user);
            $entlMng->flush();
            $session->remove('pending_registration');
    
            $this->addFlash('success', 'Регистрация прошла успешно. Теперь можно войти.');
            return $this->redirectToRoute('login');
        }

        return $this->render('Authentication/Registration/verify_email.html.twig', [
            'verificationForm' => $form,
        ]);
    }

    private function sendVerificationEmail(string $emailAddress, string $code, MailerInterface $mailer): void
    {
        $email = (new TemplatedEmail())
            ->from(new Address($this->mailerFromEmail, $this->mailerFromName))
            ->to($emailAddress)
            ->subject('Код подтверждения регистрации')
            ->htmlTemplate('Authentication/Registration/email.html.twig')
            ->context([
                'code' => $code,
            ])
        ;

        $mailer->send($email);
    }
}
