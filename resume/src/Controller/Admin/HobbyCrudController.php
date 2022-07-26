<?php

namespace App\Controller\Admin;

use App\Entity\Hobby;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class HobbyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Hobby::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Hobby')
            ->setEntityLabelInPlural('Hobbies')
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['name'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name');
    }
}
