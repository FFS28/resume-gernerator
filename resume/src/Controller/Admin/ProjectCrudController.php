<?php

namespace App\Controller\Admin;

use App\Entity\Project;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Field\UrlField;

class ProjectCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Project::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Project')
            ->setEntityLabelInPlural('Projects')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'description', 'websiteUrl', 'sourceUrl', 'skills'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('name')->setColumns(2);
        yield UrlField::new('websiteUrl')->setColumns(5);
        yield UrlField::new('sourceUrl')->setColumns(5);

        yield TextareaField::new('description')->setColumns(12);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield TextField::new('description')->setColumns(2);
            yield AssociationField::new('skills')->setColumns(4);
        } else {
            yield ArrayField::new('skills');
        }
    }
}
