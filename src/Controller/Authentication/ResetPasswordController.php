<?php

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Service\ResetPasswordService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use SymfonyCasts\Bundle\ResetPassword\Controller\ResetPasswordControllerTrait;
use SymfonyCasts\Bundle\ResetPassword\Exception\ResetPasswordExceptionInterface;
use SymfonyCasts\Bundle\ResetPassword\ResetPasswordHelperInterface;

#[Route('/{_locale}/reset-password')]
class ResetPasswordController extends AbstractController
{
    use ResetPasswordControllerTrait;

    public function __construct(private ResetPasswordHelperInterface $resetPasswordHelper, private EntityManagerInterface $entityManager, private ResetPasswordService $resetPasswordService) 
    {
    }

    #[Route('/', name: 'app_forgot_password_request')]
    public function request(Request $request, TranslatorInterface $translator): Response
    {
        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            /** @var string $email */
            $email = $form->get('email')->getData();
            $request->getSession()->set('reset_email', $email);

            $resetToken = $this->resetPasswordService->generateAndSendResetToken($email);
            return $this->redirectToRoute('app_check_email');
        }

        return $this->render('Authentication/ResetPassword/request.html.twig', ['requestForm' => $form,]);
    }

    #[Route('/check-email', name: 'app_check_email')]
    public function checkEmail(Request $request): Response
    {
        $session = $request->getSession();

        $email = $session->get('reset_email');
        $resetToken = $this->getTokenObjectFromSession();

        if (!$email && !$resetToken) 
        {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        if (null === $resetToken)
        {
            $resetToken = $this->resetPasswordHelper->generateFakeResetToken();
        }

        return $this->render('Authentication/ResetPassword/check_email.html.twig', 
        [
            'resetToken' => $resetToken,
            'email' => $email,
        ]);
    }

    #[Route('/reset/{token}', name: 'app_reset_password')]
    public function reset(Request $request, UserPasswordHasherInterface $passwordHasher, TranslatorInterface $translator, ?string $token = null): Response
    {
        if ($token) 
        {
            $this->storeTokenInSession($token);
            return $this->redirectToRoute('app_reset_password');
        }

        $token = $this->getTokenFromSession();
        if (null === $token) 
        {
            $this->addFlash('error', 'notifications.error.invalid_reset_link');
            return $this->redirectToRoute('login');
        }

        try 
        {
            /** @var User $user */
            $user = $this->resetPasswordHelper->validateTokenAndFetchUser($token);

        } catch (ResetPasswordExceptionInterface $e) 
        {
            $this->addFlash('reset_password_error', sprintf(
                '%s - %s',
                $translator->trans(ResetPasswordExceptionInterface::MESSAGE_PROBLEM_VALIDATE, [], 'ResetPasswordBundle'),
                $translator->trans($e->getReason(), [], 'ResetPasswordBundle')
            ));

            return $this->redirectToRoute('app_forgot_password_request');
        }

        $form = $this->createForm(ChangePasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) 
        {
            $this->resetPasswordHelper->removeResetRequest($token);

            /** @var string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($passwordHasher->hashPassword($user, $plainPassword));
            $this->entityManager->flush();
            $this->cleanSessionAfterReset();
            $this->addFlash('success', 'notifications.success.password_changed');
            
            return $this->redirectToRoute('login');
        }

        return $this->render('Authentication/ResetPassword/reset.html.twig', ['resetForm' => $form,]);
    }

}
