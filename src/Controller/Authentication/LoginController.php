<?php

namespace App\Controller\Authentication;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\ResetPasswordType;

use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route("/login", name: "login")]
    public function login(AuthenticationUtils $authUtils): Response
    {
        // Проверяем, авторизован ли пользователь
        if ($this->isGranted('IS_AUTHENTICATED_FULLY')) 
        {
            return $this->redirectToRoute('admin');
        }

        return $this->render('Authentication/login.html.twig', 
        [
            'error' => $authUtils->getLastAuthenticationError(),
            'last_username' => $authUtils->getLastUsername(),
            'csrf_token_intention' => 'authenticate',
            'forgot_password_enabled' => true,
            'target_path' => $this->generateUrl('admin'),
            'forgot_password_path' => $this->generateUrl("ResetPassword"),
        ]);
    }

    #[Route('/logout', name: 'logout')]
    public function logout(): void
    {
        throw new \LogicException();
    }

    #[Route('/registration', name: 'registration')]
    public function registration(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entlMng): Response
    {
        $user = new User();
        $form = $this -> createForm(RegistrationFormType::class, $user);
        $form -> handleRequest($request);

        if ($form -> isSubmitted() && $form -> isValid()) 
        {
            $existingUser = $entlMng->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);
            if ($existingUser)
            {
                $form->addError(new FormError('Пользователь с таким логином уже зарегистрирован'));
            }
            else
            {
                $password = $form -> get('password') -> getData();

                // encode the plain password
                $user->setPassword($passwordHasher -> hashPassword($user, $password));
                $user->setRoles(['ROLE_USER']);
                
                $entlMng->persist($user);
                $entlMng->flush();
                return $this -> redirectToRoute('login');
            }
        }
        
        return $this -> render('Authentication/registration.html.twig', 
        [
            'registrationForm' => $form,
        ]);
    }

    #[Route('/ResetPassword', name: 'ResetPassword')]
    public function ResetPassword(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entlMng): Response
    {
        $user = new User();
        $form = $this->createForm(ResetPasswordType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
        {    
           $existingUser = $entlMng->getRepository(User::class)->findOneBy(['username' => $user->getUsername()]);
            if (!$existingUser)
            {
                $form->addError(new FormError('Пользователь с таким логином не существует'));
            }
            else
            {
                $password = $form->get('password')->getData();
                $existingUser->setPassword($passwordHasher->hashPassword($user, $password));

                $entlMng->flush();
                return $this->redirectToRoute('login');
            }
        }

        return $this->render('Authentication/resetPass.html.twig', [
            'registrationForm' => $form,
        ]);
    }

    #[Route("/")]
    public function homePage(AuthenticationUtils $authenticationUtils): Response
    {
        return $this -> redirect("login");
    }

}
