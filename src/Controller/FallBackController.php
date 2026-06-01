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

        return $this->redirectToRoute('login');
    }
}