<?php

namespace App\Controller\AdminPanel;

use App\Entity\Table;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class TableCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Table::class;
    }

    public function deleteEntity(EntityManagerInterface $entlMng, $entInst): void
    {
        if (!$entInst instanceof Table) {
            return;
        }

        foreach ($entInst->getListGuests() as $guest) {
            $guest->setTable(null);
        }

        $entlMng->flush();
        parent::deleteEntity($entlMng, $entInst);
    }

    public function configureFields(string $pageName): iterable
    {
        $fields = [
            IdField::new("id")->onlyOnIndex(),
            IntegerField::new("numTable"),
            TextField::new("description"),
            IntegerField::new("maxGuests"),
        ];

        if ($pageName === Crud::PAGE_INDEX || $pageName === Crud::PAGE_DETAIL) {
            $fields[] = IntegerField::new("guests")->onlyOnIndex();
            $fields[] = IntegerField::new("presentGuests")->onlyOnIndex();
        }

        /*
        elseif($pageName === Crud::PAGE_EDIT || $pageName === Crud::PAGE_NEW)
        { }
        */

        return $fields;
    }
}
