<?php

namespace App\Controller\Admin;

use App\Entity\Person;
use App\Enum\PersonCivilityEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class PersonCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Person::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Person')
            ->setEntityLabelInPlural('Persons')
            ->setDefaultSort(['lastname' => 'ASC'])
            ->setSearchFields(['firstname', 'lastname', 'emails', 'company.name'])
            ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('company')->setColumns(2);

        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_DETAIL === $pageName) {
            yield ChoiceField::new('civility')
                ->setTranslatableChoices([...PersonCivilityEnum::choices()]);
        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield ChoiceField::new('civility')->setColumns(1)
                ->setChoices([...PersonCivilityEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => PersonCivilityEnum::class]);
        }

        yield TextField::new('firstname')->setColumns(2);
        yield TextField::new('lastname')->setColumns(2);
        yield ArrayField::new('emails')->setColumns(2);
        yield ArrayField::new('phones')->setColumns(2);
        yield BooleanField::new('isInvoicingDefault')->setColumns(2);
    }
}
