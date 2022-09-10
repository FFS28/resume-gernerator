<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use App\Filter\DateMonthFilter;
use App\Filter\DateQuarterFilter;
use App\Filter\DateYearFilter;
use App\Filter\EnumFilter;
use App\Form\Filter\OperationTypeFilterType;
use App\Repository\OperationFilterRepository;
use App\Service\FlashbagService;
use App\Service\StatementService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\RedirectResponse;

class OperationCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly OperationFilterRepository $operationFilterRepository,
        private readonly StatementService          $statementService,
        private readonly FlashbagService           $flashbagService
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Operation::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Operation')
            ->setEntityLabelInPlural('Operations')
            ->setDefaultSort(['type' => 'DESC'])
            ->setPaginatorPageSize(40)
            ->setSearchFields(['date', 'type', 'label', 'target', 'location', 'amount']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $analyzeAction = Action::new('analyze', 'Analyze', 'fa fa-check')
            ->linkToCrudAction('analyzeAction')
            ->addCssClass('btn-sm btn-success');

        $actions
            ->addBatchAction($analyzeAction);

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(DateYearFilter::new('date', 'Created At year'))
            ->add(DateMonthFilter::new('date', 'Created At month'))
            ->add(EnumFilter::new('type', OperationTypeFilterType::class))
            ->add('label')
            ->add('target')
            ->add('name')
            ->add('location')
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield DateField::new('date');
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...OperationTypeEnum::choices()]);

            yield TextField::new('label');
            yield TextField::new('target');
            yield TextField::new('name');
            yield TextField::new('location');
            yield MoneyField::new('amount')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2);

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Operation');
            yield DateField::new('date')->setColumns(2);
            yield TextField::new('name')->setColumns(8);
            yield MoneyField::new('amount')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2);

            yield FormField::addPanel('Categories');
            yield ChoiceField::new('type')->setColumns(2)
                ->setChoices([...OperationTypeEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => OperationTypeEnum::class]);
            yield TextField::new('label')->setColumns(2);

            yield FormField::addPanel('Informations');
            yield TextField::new('target')->setColumns(2);
            yield TextField::new('location')->setColumns(2);
        }
    }

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function analyzeAction(BatchActionDto $batchActionDto): RedirectResponse
    {
        /** @var EntityManagerInterface $entityManager */
        $entityManager = $this->container->get('doctrine')->getManagerForClass($batchActionDto->getEntityFqcn());
        $filters = $this->operationFilterRepository->getFilters();

        foreach ($batchActionDto->getEntityIds() as $id) {
            /** @var Operation $operation */
            $operation = $entityManager->find($batchActionDto->getEntityFqcn(), $id);

            if ($operation) {
                $this->statementService->analyseOperation($operation, $filters);
            }
        }

        $entityManager->flush();

        $this->flashbagService->send('operations_analyzed');
        return $this->redirect($batchActionDto->getReferrerUrl());
    }
}
