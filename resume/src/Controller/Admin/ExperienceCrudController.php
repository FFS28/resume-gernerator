<?php

namespace App\Controller\Admin;

use App\Entity\Experience;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class ExperienceCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Experience::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Experience')
            ->setEntityLabelInPlural('Experiences')
            ->setDefaultSort(['dateBegin' => 'DESC'])
            ->setSearchFields(['company.name', 'dateBegin', 'dateEnd'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {

        if (Crud::PAGE_INDEX === $pageName) {
            yield AssociationField::new('company');
            yield AssociationField::new('client');
            yield DateField::new('dateBegin');
            yield DateField::new('dateEnd');
            yield BooleanField::new('onHomepage');
            yield ArrayField::new('mainSkills');

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Informations');
            yield TextField::new('title')->setColumns(2);
            yield AssociationField::new('company')->setColumns(2);
            yield AssociationField::new('client')->setColumns(2);

            yield FormField::addPanel('Mission');
            yield TextareaField::new('description')->setColumns(4);
            yield DateField::new('dateBegin')->setColumns(2);
            yield DateField::new('dateEnd')->setColumns(2);
            yield AssociationField::new('skills')->setColumns(4);

            yield FormField::addPanel('Parameters');
            yield BooleanField::new('isFreelance')->setColumns(2);
            yield BooleanField::new('onSite')->setColumns(2);
            yield BooleanField::new('onHomepage')->setColumns(2);
        }
    }
}
