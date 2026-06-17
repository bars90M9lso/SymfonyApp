<?php

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\EmailVerificationCodeFormType;
use App\Service\RegistrationService;
use App\Service\UserManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/{_locale}/registration')]
class RegistrationController extends AbstractController
{
    private const SESSION_KEY = 'pending_registration';

    private function buildVerificationViewData(?array $pending): array
    {
        if (!$pending) 
        {
            return 
            [
                'locked' => false,
                'attempts' => 0,
                'resendSeconds' => 0,
            ];
        }

        $locked = $pending['locked'] ?? false;

        return 
        [
            'locked' => $locked,
            'attempts' => $pending['attempts'] ?? 0,
            'resendSeconds' => $locked ? 0 : max(0, ($pending['resend_available_at'] ?? 0) - time()),
        ];
    }

    public function __construct(private RegistrationService $registrationService, private UserManager $userManager)
    {
    }

    #[Route('/', name: 'registration')]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $passwordHash = $passwordHasher->hashPassword($user, (string) $form->get('password')->getData());
            $this->registrationService->startRegistration($user->getUsername(), $user->getEmail(), $passwordHash, $request->getSession());
            return $this->redirectToRoute('registration_verify');
        }

        return $this->render('Authentication/Registration/registration.html.twig', ['registrationForm' => $form, ]);

    }

    #[Route('/verify', name: 'registration_verify')]
    public function verify(Request $request): Response
    {
        $session = $request->getSession();
        $pending = $session->get(self::SESSION_KEY);

        if (!$pending || empty($pending['email']) || empty($pending['username']) || empty($pending['code_hash']))
        {
            return $this->redirectToRoute('registration');
        }
        
        $viewData = $this->buildVerificationViewData($pending);
        $locked = $viewData['locked'];
        $attempts = $viewData['attempts'];
        $resendSeconds = $viewData['resendSeconds'];

        $form = $this->createForm(EmailVerificationCodeFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $inputCode = $form->get('code')->getData();

            try 
            {
                $this->registrationService->confirmRegistration($inputCode, $request->getSession());
                $this->addFlash('success', 'notifications.success.success_registration');
                
                return $this->redirectToRoute('registration_exit');

            } catch (\RuntimeException $e) 
            {
                $this->addFlash('error', $e->getMessage());

                $pending = $session->get(self::SESSION_KEY);
                
                $viewData = $this->buildVerificationViewData($pending);

                $locked = $viewData['locked'];
                $attempts = $viewData['attempts'];
                $resendSeconds = $viewData['resendSeconds'];
            }
        }

        return $this->render('Authentication/Registration/verify_email.html.twig', 
        [
            'verificationForm' => $form,
            'resendSeconds' => $resendSeconds,
            'locked' => $locked,
            'attempts' => $attempts,
        ]);
    }

    #[Route('/resend-code', name: 'registration_resend_code')]
    public function resendCode(Request $request): Response
    {
        try 
        {
            $this->registrationService->resendCode($request->getSession());
            $this->addFlash('success', 'notifications.success.code_sent');

        } catch (\RuntimeException $e) 
        {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('registration');
        }

        return $this->redirectToRoute('registration_verify');
    }

    #[Route('/exit', name: 'registration_exit')]
    public function exit(Request $request): Response
    {
        $session = $request->getSession();
        $session->remove(self::SESSION_KEY);
        return $this->redirectToRoute('login');
    }
}
