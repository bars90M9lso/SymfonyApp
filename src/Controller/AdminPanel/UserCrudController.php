<?php

namespace App\Controller\AdminPanel;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;

#[IsGranted('ROLE_ADMIN')]
class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function persistEntity(EntityManagerInterface $entlMng, $entInst): void
    {
        if (!$entInst instanceof User) { return; }

        $this->hashPlainPassword($entInst);
        parent::persistEntity($entlMng, $entInst);
    }

    public function updateEntity(EntityManagerInterface $entlMng, $entInst): void
    {
        if (!$entInst instanceof User) { return; }

        $this->hashPlainPassword($entInst);
        parent::updateEntity($entlMng, $entInst);
    }

    private function hashPlainPassword(User $user): void
    {
        if (!$user->getPlainPassword()) { return; }

        $user->setPassword($this->passwordHasher->hashPassword($user, $user->getPlainPassword()));
    }

    public function configureFields(string $pageName): iterable
    {
        $fields =
        [
            IdField::new('id')->onlyOnIndex(),
            TextField::new('username'),
            TextField::new('email'),
        ];

        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) 
        {
            $fields[] = ArrayField::new('roles');
            
        } elseif ($pageName === Crud::PAGE_EDIT) 
        {
            $fields[] = ChoiceField::new('roles')
                        ->allowMultipleChoices()
                        ->setChoices([
                            'roles.admin' => 'ROLE_ADMIN',
                            'roles.user' => 'ROLE_USER',
                        ]);

            $fields[] = TextField::new('plainPassword')->onlyOnForms();

        } elseif ($pageName === Crud::PAGE_NEW) 
        {
            $fields[] = ChoiceField::new('roles')
                        ->allowMultipleChoices()
                        ->setChoices([
                            'roles.admin' => 'ROLE_ADMIN',
                            'roles.user' => 'ROLE_USER',
                        ]);

            $fields[] = TextField::new('plainPassword')->onlyOnForms()->setRequired(true);
        }

        return $fields;
    }
}
