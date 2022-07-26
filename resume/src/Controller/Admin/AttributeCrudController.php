<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class AttributeCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Attribute::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular(\Attribute::class)
            ->setEntityLabelInPlural('Attributes')
            ->setDefaultSort(['isListable' => 'ASC', 'weight' => 'DESC'])
            ->setSearchFields(['name', 'slug', 'icon'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('slug')->setColumns(2);
        yield TextField::new('value')->setColumns(5);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield TextField::new('icon')->setColumns(1);
        }

        yield NumberField::new('weight')->setColumns(1);
        yield BooleanField::new('isListable')->setColumns(12);
    }
}
