<?php

namespace App\Controller\Admin;

use App\Entity\OperationFilter;
use App\Enum\OperationTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class OperationFilterCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OperationFilter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Filter')
            ->setEntityLabelInPlural('Filters')
            ->setDefaultSort(['type' => 'ASC'])
            ->setSearchFields(['name', 'type', 'label', 'target', 'date', 'amount'])
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Operation');
        yield TextField::new('name');

        yield FormField::addPanel('Categories');
        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...OperationTypeEnum::choices()]);

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield ChoiceField::new('type')->setColumns(2)
                ->setChoices([...OperationTypeEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => OperationTypeEnum::class]);
        }
        yield TextField::new('label')->setColumns(2);

        yield FormField::addPanel('Informations');
        yield TextField::new('target')->setColumns(2);
        yield DateField::new('date')->setColumns(2);
        yield MoneyField::new('amount')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2);
    }
}
