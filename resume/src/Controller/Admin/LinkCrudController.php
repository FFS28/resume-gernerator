<?php

namespace App\Controller\Admin;

use App\Entity\Link;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class LinkCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Link::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Link')
            ->setEntityLabelInPlural('Links')
            ->setDefaultSort(['id' => 'ASC'])
            ->setSearchFields(['name', 'icon', 'url'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('icon')->setColumns(2);
        }
        yield TextField::new('name')->setColumns(2);
        yield UrlField::new('url')->setColumns(4);
    }
}
