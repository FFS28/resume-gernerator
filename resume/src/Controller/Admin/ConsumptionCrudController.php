<?php

namespace App\Controller\Admin;

use App\Entity\Consumption;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ConsumptionCrudController extends AbstractCrudController
{
    public function __construct() {}

    public static function getEntityFqcn(): string
    {
        return Consumption::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Consumption')
            ->setEntityLabelInPlural('Consumptions')
            ->setDefaultSort(['date' => 'DESC'])
            ->setSearchFields(['consumptionMonth', 'day', 'date'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->addBatchAction(Action::BATCH_DELETE);
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_EDIT === $pageName) {
            yield AssociationField::new('consumptionMonth')->setFormTypeOption('disabled','disabled');
            yield DateField::new('date')->setFormTypeOption('disabled','disabled');
        } else {
            yield AssociationField::new('consumptionMonth');
            yield DateField::new('date');
        }
        yield NumberField::new('meterLowHour');
        yield NumberField::new('meterFullHour');
        yield NumberField::new('meterWeekendHour');
        yield NumberField::new('diffLowHour');
        yield NumberField::new('diffFullHour');
        yield NumberField::new('diffWeekendHour');
        yield NumberField::new('diffTotal');
    }
}
