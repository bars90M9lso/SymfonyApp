<?php

namespace App\Controller\Authentication;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route("/login", name: "login")]
    public function login(AuthenticationUtils $authUtils): Response
    {
        // Проверяем, авторизован ли пользователь
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) {
            return $this->redirectToRoute('admin');
        }

        return $this->render('Authentication/login.html.twig',
        [
            'error' => $authUtils->getLastAuthenticationError(),
            'last_username' => $authUtils->getLastUsername(),
            'csrf_token_intention' => 'authenticate',
            'forgot_password_enabled' => true,
            'target_path' => $this->generateUrl('admin'),
            'forgot_password_path' => $this->generateUrl("app_forgot_password_request"),
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }
}
