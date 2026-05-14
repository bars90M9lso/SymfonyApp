<?php

namespace App\Controller\AdminPanel;

use App\Entity\ListGuest;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class ListGuestCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return ListGuest::class;
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [ ];
        
        if ($pageName === Crud::PAGE_INDEX or $pageName === Crud::PAGE_DETAIL)
        {
            $fields = 
            [
                idField::new("id"),
                BooleanField::new("isPresent"),
                TextField::new("name"),
                AssociationField::new('tables'),
            ];
        }

        elseif($pageName === Crud::PAGE_EDIT or $pageName === Crud::PAGE_NEW)
        {
            $fields =  
            [
                BooleanField::new("isPresent"),
                TextField::new("name"),
                AssociationField::new('tables'),
            ];
        }

        return $fields;   
    }
}
