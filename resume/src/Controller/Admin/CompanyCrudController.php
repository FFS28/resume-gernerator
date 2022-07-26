<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Enum\CompanyTypeEnum;
use App\Form\Type\PersonType;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class CompanyCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Company::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Company')
            ->setEntityLabelInPlural('Companies')
            ->setDefaultSort(['name' => 'ASC'])
            ->setSearchFields(['name', 'displayName', 'type', 'contractor.name', 'clients.name',
                               'persons.firstname', 'persons.lastname', 'persons.emails'])
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('displayName', 'Name');
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...CompanyTypeEnum::choices()]);
            yield CollectionField::new('clients');
            yield AssociationField::new('contractor');
            yield CollectionField::new('persons');
            yield TextField::new('notes');

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield FormField::addPanel('Informations');
            yield TextField::new('name')->setColumns(2);
            yield TextField::new('displayName')->setColumns(2);

            if (Crud::PAGE_DETAIL === $pageName) {
                yield ChoiceField::new('type')
                    ->setTranslatableChoices([...CompanyTypeEnum::choices()]);
                yield CollectionField::new('persons');
            } else {
                yield ChoiceField::new('type')->setColumns(1)
                    ->setChoices([...CompanyTypeEnum::cases()])
                    ->setFormType(EnumType::class)
                    ->setFormTypeOptions(['class' => CompanyTypeEnum::class]);
            }

            yield AssociationField::new('clients')->setColumns(2);
            yield AssociationField::new('contractor')->setColumns(2);
            yield MoneyField::new('tjm')->setColumns(1)->setCurrency('EUR')->setStoredAsCents(false);
            yield TextField::new('reference')->setColumns(2);

            yield FormField::addPanel('Address')->setColumns(2);
            yield TextField::new('service')->setColumns(2);
            yield TextField::new('street')->setColumns(2);
            yield TextField::new('postalCode')->setColumns(1);
            yield TextField::new('city')->setColumns(2);
            yield AssociationField::new('persons')->setColumns(2);
            yield TextField::new('notes')->setColumns(3);

            yield FormField::addPanel('Invoices');
            yield ArrayField::new('invoices')
                ->setDisabled()
                ->setLabel(false)
                ->setFormTypeOption('block_name', 'invoices')
                ->setFormTypeOption('allow_add', false)
                ->setFormTypeOption('allow_delete', false)
                ->setColumns(12)
            ;
        }
    }
}
