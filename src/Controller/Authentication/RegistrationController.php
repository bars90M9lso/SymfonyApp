<?php

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\EmailVerificationCodeFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use App\Service\RegistrationService;
use App\Service\UserManager;

class RegistrationController extends AbstractController
{
    public function __construct(private RegistrationService $registrationService, private UserManager $userManager)
    {
    }

    #[Route('/registration', name: 'registration')]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $existingUser = $this->userManager->findByUsername($user->getUsername());
            $existingEmail = $this->userManager->findByEmail($user->getEmail());

            if ($existingUser) {
                $form->addError(new FormError('Пользователь с таким логином уже зарегистрирован'));
                return $this->render('Authentication/Registration/registration.html.twig', ['registrationForm' => $form,]);
            }
            if ($existingEmail) {
                $form->addError(new FormError('Пользователь с таким email уже зарегистрирован'));
                return $this->render('Authentication/Registration/registration.html.twig', ['registrationForm' => $form,]);
            }

            $passwordHash = $passwordHasher->hashPassword($user, (string) $form->get('password')->getData());
            $this->registrationService->startRegistration($user->getUsername(), $user->getEmail(), $passwordHash, $request->getSession());
            return $this->redirectToRoute('registration_verify');
        }

        return $this->render('Authentication/Registration/registration.html.twig', [
            'registrationForm' => $form,
        ]);

    }

    #[Route('/registration/verify', name: 'registration_verify')]
    public function verify(Request $request, EntityManagerInterface $entlMng): Response
    {
        $session = $request->getSession();
        $pending = $session->get('pending_registration');

        if (!$pending) {
            $this->addFlash('error', 'Нет данных для подтверждения регистрации. Пожалуйста, зарегистрируйтесь заново.');
            return $this->redirectToRoute('registration');
        }

        $form = $this->createForm(EmailVerificationCodeFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $inputCode = $form->get('code')->getData();

            try {
                $this->registrationService->confirmRegistration($inputCode, $request->getSession());
                $this->addFlash('success', 'Регистрация прошла успешно. Теперь можно войти.');
                return $this->redirectToRoute('login');
            } catch (\RuntimeException $e) {
                $this->addFlash('error', $e->getMessage());
                return $this->redirectToRoute('registration');
            }
        }

        return $this->render('Authentication/Registration/verify_email.html.twig', [
            'verificationForm' => $form,
        ]);
    }
}
