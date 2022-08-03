<?php

namespace App\Controller\Admin;

use App\Entity\Statement;
use App\Service\FlashbagService;
use App\Service\StatementService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Contracts\Translation\TranslatorInterface;
use Vich\UploaderBundle\Form\Type\VichImageType;

class StatementCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly StatementService    $statementService,
        private readonly TranslatorInterface $translator,
        private readonly FlashbagService     $flashbagService
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Statement::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Statement')
            ->setEntityLabelInPlural('Statements')
            ->setDefaultSort(['date' => 'DESC'])
            ->setSearchFields(['date'])
            ;
    }

    public function configureActions(Actions $actions): Actions
    {
        $ocrAction = Action::new('ocr', 'Ocr', 'fa fa-eye')
            ->linkToCrudAction('ocrAction')
            ->displayIf(fn(Statement $statement) =>
                $statement->getOperationsCount() == 0 ||
                $statement->getStartAmount() == null ||
                $statement->getEndAmount() == null ||
                $statement->getGapAmount() == null ||
                $statement->getSavingAmount() == null
            )
            ->addCssClass('btn-sm btn-success');

        $downloadAction = Action::new('download', 'Download', 'fa-solid fa-file-pdf')
            ->linkToCrudAction('downloadAction')
            ->setHtmlAttributes(['target' => '#blank'])
            ->addCssClass('btn-sm btn-success');

        $actions
            ->add(Crud::PAGE_INDEX, $downloadAction)
            ->add(Crud::PAGE_EDIT, $downloadAction)
            ->add(Crud::PAGE_INDEX, $ocrAction)
            ->add(Crud::PAGE_EDIT, $ocrAction);

        return $actions;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName || Crud::PAGE_EDIT === $pageName) {
            yield DateField::new('date');
        }
        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield TextareaField::new('file')->setFormType(VichImageType::class);
        }
        if (Crud::PAGE_INDEX === $pageName) {
            yield NumberField::new('operationsCount', 'Operations');
            yield NumberField::new('startAmount');
            yield NumberField::new('endAmount');
            yield NumberField::new('gapAmount');
            yield NumberField::new('savingAmount');
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    public function ocrAction(AdminContext $context): RedirectResponse
    {
        $declaration = null;
        /** @var Statement $declaration */
        $statement = $context->getEntity()->getInstance();

        $this->extractWithMessage($statement, true);

        return $this->redirect($context->getReferrer());
    }

    /**
     * @throws NonUniqueResultException
     */
    public function downloadAction(AdminContext $context): Response
    {
        /** @var Statement $declaration */
        $statement = $context->getEntity()->getInstance();

        return $this->file(
            $this->statementService->get($statement),
            $statement->getFilename(),
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    /**
     * @param Statement $entityInstance
     * @throws NonUniqueResultException
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        parent::persistEntity($entityManager, $entityInstance);

        if (!$entityInstance->getDate()) {
            $matches = [];

            preg_match(
                '#Extrait de comptes Compte \d+ \d+.. C_C EUROCOMPTE DUO CONFORT M ACHAIN JEREMY au (\d{4}-\d{2}-\d{2})#i',
                $entityInstance->getFilename(), $matches
            );

            if (count($matches) === 2) {
                $entityInstance->setDate(DateTime::createFromFormat('Y-m-d', $matches[1]));
            } else {
                $entityInstance->setDate(new DateTime());
            }

            $entityManager->flush();
        }

        if ($entityInstance->getOperationsCount() == 0) {
            $this->extractWithMessage($entityInstance, false);
        }
    }

    /**
     * @throws NonUniqueResultException
     */
    private function extractWithMessage(Statement $entityInstance, bool $throwError)
    {
        $this->statementService->extractOperations($entityInstance, $throwError);
        $operationCount = $entityInstance->getOperationsCount();
        $entityInstance->setTranslator($this->translator);

        if ($operationCount === 0) {
            $this->flashbagService->send(
                'statement_analyzed_error',
                $entityInstance,
                [],
                'danger'
            );
        } else {
            $this->flashbagService->send(
                'statement_analyzed_success',
                $entityInstance,
                ['%operationsCount%' => $operationCount]
            );
        }
    }
}
