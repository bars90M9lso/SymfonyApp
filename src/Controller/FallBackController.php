<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class FallBackController extends AbstractController
{
    #[Route('/{any}', name: 'fallback', requirements: ['any' => '.*'], priority: -100)]
    public function index(?string $any): Response
    {
        if (str_starts_with($any, 'adm')) {
            return $this->redirectToRoute('admin');
        }

        if (str_starts_with($any, 'reg')) {
            return $this->redirectToRoute('registration');
        }

        if (str_starts_with($any, 'res')) {
            return $this->redirectToRoute('app_forgot_password_request');
        }

        return $this->redirectToRoute('login');
    }

    #[Route("/")]
    public function loginPage(): Response
    {
        return $this->redirectToRoute("login");
    }
}
