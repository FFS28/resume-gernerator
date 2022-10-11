<?php

namespace App\Controller\Admin;

use App\Entity\ConsumptionMonth;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;

class ConsumptionMonthCrudController extends AbstractCrudController
{
    public function __construct() {}

    public static function getEntityFqcn(): string
    {
        return ConsumptionMonth::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ConsumptionMonth')
            ->setEntityLabelInPlural('ConsumptionMonths')
            ->setDefaultSort(['year' => 'DESC', 'month' => 'DESC'])
            ->setSearchFields(['year', 'month'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->addBatchAction(Action::BATCH_DELETE)
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        yield NumberField::new('year')->setFormTypeOption('disabled','disabled');;
        yield NumberField::new('month')->setFormTypeOption('disabled','disabled');;
        yield NumberField::new('lowHour');
        yield NumberField::new('fullHour');
        yield NumberField::new('weekendHour');
        yield NumberField::new('total');
    }
}
