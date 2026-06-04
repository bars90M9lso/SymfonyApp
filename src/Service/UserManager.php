<?php

namespace App\Service;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserManager
{
    public function __construct(private EntityManagerInterface $em)
    {
    }

    public function findByUsername(string $username): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['username' => $username]);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->em->getRepository(User::class)->findOneBy(['email' => $email]);
    }

    public function createUser(string $username, string $email, string $passwordHash): User
    {
        $user = new User();
        $user->setUsername($username);
        $user->setEmail($email);
        $user->setPassword($passwordHash);

        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }

    public function changePassword(User $user, string $plainPassword, UserPasswordHasherInterface $hasher): void
    {
        $user->setPassword($hasher->hashPassword($user, $plainPassword));
        $this->em->flush();
    }
}
