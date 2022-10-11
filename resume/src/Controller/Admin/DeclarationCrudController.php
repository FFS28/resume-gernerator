<?php

namespace App\Controller\Admin;

use App\Entity\Declaration;
use App\Enum\DeclarationStatusEnum;
use App\Enum\DeclarationTypeEnum;
use App\Filter\EnumFilter;
use App\Form\Filter\DeclarationStatusFilterType;
use App\Form\Filter\DeclarationTypeFilterType;
use App\Service\DeclarationService;
use App\Service\FlashbagService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\PercentField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class DeclarationCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeclarationService     $declarationService,
        private readonly FlashbagService        $flashbagService
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Declaration::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Declaration')
            ->setEntityLabelInPlural('Declarations')
            ->setDefaultSort(['period' => 'DESC'])
            ->setSearchFields(['type', 'period.year', 'status'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $validateAction = Action::new('validate', 'Validate', 'fa fa-check')
            ->linkToCrudAction('validateAction')
            ->displayIf(fn(Declaration $declaration) => $declaration->getStatus() === DeclarationStatusEnum::Waiting)
            ->addCssClass('btn-sm btn-success');

        $calculateAction = Action::new('calculate', 'Recalculate', 'fa fa-calculator')
            ->linkToCrudAction('calculateAction')
            ->displayIf(fn(Declaration $declaration) => $declaration->getStatus() === DeclarationStatusEnum::Waiting)
            ->addCssClass('btn-sm btn-success');

        $actions
            ->add(Crud::PAGE_INDEX, $validateAction)
            ->add(Crud::PAGE_INDEX, $calculateAction)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
        ;

        $actions->disable(Action::BATCH_DELETE);

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EnumFilter::new('type', DeclarationTypeFilterType::class))
            ->add('period')
            ->add(EnumFilter::new('status', DeclarationStatusFilterType::class))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...DeclarationTypeEnum::choices()]);
            yield MoneyField::new('revenue')->setCurrency('EUR')->setStoredAsCents(false);
            yield MoneyField::new('tax')->setCurrency('EUR')->setStoredAsCents(false);
            yield PercentField::new('rate')->setStoredAsFractional(false);
            yield AssociationField::new('period');
            yield ChoiceField::new('status')
                ->setTranslatableChoices([...DeclarationStatusEnum::choices()]);

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Informations');
            yield ChoiceField::new('type')->setColumns(2)
                ->setChoices([...DeclarationTypeEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => DeclarationTypeEnum::class]);
            yield AssociationField::new('period')->setColumns(2);
            yield MoneyField::new('revenue')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false);
            yield MoneyField::new('tax')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false);
            yield ChoiceField::new('status')->setColumns(2)
                ->setChoices([...DeclarationStatusEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => DeclarationStatusEnum::class]);
            yield DateField::new('payedAt')->setColumns(2);

            yield FormField::addPanel('Invoices');
            yield ArrayField::new('invoices')
                ->setDisabled()
                ->setLabel(false)
                ->setFormTypeOption('block_name', 'invoices')
                ->setFormTypeOption('allow_add', false)
                ->setFormTypeOption('allow_delete', false)
                ->setColumns(12);
        }
    }

    public function validateAction(AdminContext $context): RedirectResponse
    {
        /** @var Declaration $declaration */
        $declaration = $context->getEntity()->getInstance();

        $declaration->setStatus(DeclarationStatusEnum::Payed);
        $declaration->setPayedAt(new DateTime('now'));

        $this->entityManager->flush();

        $this->flashbagService->send('mark_as_payed', $declaration);
        return $this->redirect($context->getReferrer());
    }

    public function calculateAction(AdminContext $context): RedirectResponse
    {
        /** @var Declaration $declaration */
        $declaration = $context->getEntity()->getInstance();

        $this->declarationService->calculate($declaration);

        $this->flashbagService->send('declaration_calculated', $declaration);
        return $this->redirect($context->getReferrer());
    }

    /**
     * @param Declaration $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);
        if (!$entityInstance->getRevenue() && !$entityInstance->getTax()) {
            $this->declarationService->calculate($entityInstance);
        }
    }
}
