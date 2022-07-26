<?php

namespace App\Controller\Admin;

use App\Entity\Education;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class EducationCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Education::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Education')
            ->setEntityLabelInPlural('Educations')
            ->setDefaultSort(['dateEnd' => 'DESC'])
            ->setSearchFields(['name', 'school', 'location', 'dateBegin', 'dateEnd'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Informations');
        yield TextField::new('name')->setColumns(3);
        yield TextField::new('school')->setColumns(3);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield TextField::new('details')->setColumns(2);
        }

        yield TextField::new('location')->setColumns(2);
        yield NumberField::new('level')->setColumns(1);

        yield FormField::addPanel('Dates');
        yield DateField::new('dateBegin')->setColumns(2);
        yield DateField::new('dateEnd')->setColumns(2);
    }
}
