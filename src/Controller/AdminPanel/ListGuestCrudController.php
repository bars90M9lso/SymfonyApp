<?php

namespace App\Controller\AdminPanel;

use App\Entity\ListGuest;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
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
        $fields = [
            IdField::new("id")->onlyOnIndex(), 
            BooleanField::new("isPresent"),
            TextField::new("name"),
            AssociationField::new('table'),
        ];

        /*
        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) { }

        elseif($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW) { }
        */
        
        return $fields;   
    }
}
