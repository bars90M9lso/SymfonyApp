<?php

namespace App\Controller\AdminPanel;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField; 


class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }
    
    public function configureFields(string $pageName): iterable
    {
        $fields = [ ];
        
        if ($pageName === Crud::PAGE_INDEX or $pageName === Crud::PAGE_DETAIL)
        {
            $fields = 
            [
            IdField::new('id'),
            TextField::new('username'),
            ArrayField::new('roles'),
            ];
        }

        elseif($pageName === Crud::PAGE_EDIT or $pageName === Crud::PAGE_NEW)
        {
            $fields =  
            [
                ArrayField::new('roles'),
            ];
        }

        return $fields;   
    }
    
}
