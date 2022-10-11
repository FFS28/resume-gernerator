<?php

namespace App\Controller\Admin;

use App\Entity\ConsumptionStatement;
use App\Service\ConsumptionService;
use App\Service\FlashbagService;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Vich\UploaderBundle\Form\Type\VichImageType;

class ConsumptionStatementCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly ConsumptionService $consumptionService,
        private readonly FlashbagService        $flashbagService,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return ConsumptionStatement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('ConsumptionStatement')
            ->setEntityLabelInPlural('ConsumptionStatements')
            ->setDefaultSort(['startDate' => 'DESC'])
            ->setSearchFields(['startDate', 'endDate', 'filename'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $downloadAction = Action::new('download', 'Download', 'fa-solid fa-file-pdf')
            ->linkToCrudAction('downloadAction')
            ->setHtmlAttributes(['target' => '#blank'])
            ->addCssClass('btn-sm btn-success');

        $actions
            ->add(Crud::PAGE_INDEX, $downloadAction)
            ->add(Crud::PAGE_EDIT, $downloadAction);

        $actions->disable(Action::BATCH_DELETE);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield TextareaField::new('file')->setFormType(VichImageType::class);
        }
        if (Crud::PAGE_INDEX === $pageName) {
            yield DateField::new('startDate');
            yield DateField::new('endDate');
        }
    }

    public function downloadAction(AdminContext $context): Response
    {
        /** @var ConsumptionStatement $consumptionStatement */
        $consumptionStatement = $context->getEntity()->getInstance();

        return $this->file(
            $this->consumptionService->get($consumptionStatement),
            $consumptionStatement->getFilename(),
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    /**
     * @param ConsumptionStatement $entityInstance
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        $count = $this->consumptionService->extractConsumptions($entityInstance);

        $this->flashbagService->send(
            'consumptions_analyzed_success',
            $entityInstance,
            ['%count%' => $count]
        );
    }
}
