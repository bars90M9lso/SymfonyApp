<?php

namespace App\Controller\AdminPanel;

use App\Entity\Tables;

use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;

class TablesCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Tables::class;
    }

    public function configureFields(string $pageName): iterable
    {   
        $fields = [ ];
        
        if ($pageName === Crud::PAGE_INDEX or $pageName === Crud::PAGE_DETAIL)
        {
            $fields = 
            [
                idField::new("id"),
                integerField::new("numTable"),
                TextField::new("description"),
                integerField::new("maxGuests"),
                integerField::new("guests"),
                integerField::new("presentGuests"),
            ];
        }

        elseif($pageName === Crud::PAGE_EDIT or $pageName === Crud::PAGE_NEW)
        {
            $fields =  
            [
                integerField::new("numTable"),
                TextField::new("description"),
                integerField::new("maxGuests"),
            ];
        }

        return $fields;   
    }
}
