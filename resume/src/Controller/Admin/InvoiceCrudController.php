<?php

namespace App\Controller\Admin;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Enum\InvoicePaymentTypeEnum;
use App\Enum\InvoiceStatusEnum;
use App\Filter\DateMonthFilter;
use App\Filter\DateQuarterFilter;
use App\Filter\DateYearFilter;
use App\Filter\EnumFilter;
use App\Form\Filter\InvoiceStatusFilterType;
use App\Repository\ActivityRepository;
use App\Repository\InvoiceRepository;
use App\Service\FlashbagService;
use App\Service\InvoiceService;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
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
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Exception;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use function Symfony\Component\Translation\t;

class InvoiceCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly MailerInterface        $mailer,
        private readonly InvoiceService         $invoiceService,
        private readonly InvoiceRepository      $invoiceRepository,
        private readonly ActivityRepository     $activityRepository,
        private readonly FlashbagService        $flashbagService,
        private readonly AdminUrlGenerator      $adminUrlGenerator,
    ) {

    }

    public static function getEntityFqcn(): string
    {
        return Invoice::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Invoice')
            ->setEntityLabelInPlural('Invoices')
            ->setDefaultSort(['createdAt' => 'DESC'])
            ->setSearchFields(['company.name', 'period.year'])
            ->showEntityActionsInlined(false);
    }

    public function configureActions(Actions $actions): Actions
    {
        $validateAction = Action::new('validate', 'Validate', 'fa fa-check')
            ->linkToCrudAction('validateAction')
            ->displayIf(fn(Invoice $invoice) => $invoice->getStatus() === InvoiceStatusEnum::Draft)
            ->addCssClass('btn-sm btn-success');

        $actionDelete = Action::new(Action::DELETE, 'Delete', 'fa fa-trash-can')
            ->displayIf(fn(Invoice $invoice) => $invoice->getStatus() === InvoiceStatusEnum::Draft)
            ->linkToCrudAction(Action::DELETE);

        $payedAction = Action::new('payed', 'Marqued as payed', 'fa fa-credit-card')
            ->linkToCrudAction('payedAction')
            ->displayIf(fn(Invoice $invoice) => $invoice->getStatus() === InvoiceStatusEnum::Waiting)
            ->addCssClass('btn-sm btn-warning');

        $pdfAction = Action::new('pdf', 'Pdf', 'fa-solid fa-file-pdf')
            ->linkToCrudAction('pdfAction')
            ->setHtmlAttributes(['target' => '#blank'])
            ->displayIf(fn(Invoice $invoice) => $invoice->getStatus() === InvoiceStatusEnum::Waiting
                || $invoice->getStatus() === InvoiceStatusEnum::Payed)
            ->addCssClass('btn-sm btn-success');

        $sendAction = Action::new('send', 'Send', 'fa fa-envelope')
            ->linkToCrudAction('sendAction')
            ->displayIf(fn(Invoice $invoice) => $invoice->getStatus() === InvoiceStatusEnum::Waiting)
            ->addCssClass('btn-sm btn-danger');

        $actions
            ->add(Crud::PAGE_INDEX, $validateAction)
            ->add(Crud::PAGE_EDIT, $validateAction)
            ->add(Crud::PAGE_INDEX, $payedAction)
            ->add(Crud::PAGE_EDIT, $payedAction)
            ->add(Crud::PAGE_INDEX, $sendAction)
            ->add(Crud::PAGE_EDIT, $sendAction)
            ->add(Crud::PAGE_INDEX, $pdfAction)
            ->add(Crud::PAGE_EDIT, $pdfAction)
            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $actionDelete);

        $actions->disable(Action::BATCH_DELETE);

        return $actions;
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('company')
            ->add('period')
            ->add(DateYearFilter::new('createdAt', 'Created At year'))
            ->add(DateQuarterFilter::new('createdAt','Created At quarter'))
            ->add(DateMonthFilter::new('createdAt','Created At month'))
            ->add(EnumFilter::new('status', InvoiceStatusFilterType::class))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield TextField::new('number');
            yield AssociationField::new('company');
            yield TextField::new('experienceCompany', 'Client');
            yield MoneyField::new('totalTtc')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalNet')->setCurrency('EUR')->setStoredAsCents(false);
            yield MoneyField::new('totalHt')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalTax')->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield NumberField::new('daysCount')->setNumDecimals(1);
            yield DateField::new('createdAt');
            yield ChoiceField::new('status')
                ->setTranslatableChoices([...InvoiceStatusEnum::choices()]);
            yield DateField::new('payedAt');
            yield TextField::new('periodName', 'Period');
            yield TextField::new('socialDeclaration', 'Declaration');

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Informations');
            yield TextField::new('number')->setColumns(2);
            yield TextField::new('reference')->setColumns(2);
            yield TextField::new('object')->setColumns(4);
            yield AssociationField::new('company')->setColumns(2);
            yield AssociationField::new('experience')->setColumns(2);

            yield FormField::addPanel('Invoicing');
            yield NumberField::new('daysCount')->setNumDecimals(1)->setColumns(1);
            yield MoneyField::new('tjm')->setColumns(1)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield TextField::new('extraLibelle')->setColumns(2);
            yield MoneyField::new('extraHt')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalHt')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);
            yield MoneyField::new('totalTax')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(0);

            yield FormField::addPanel('Status');
            yield DateField::new('createdAt')->setColumns(2);
            yield ChoiceField::new('payedBy')->setColumns(2)
                ->setChoices([...InvoicePaymentTypeEnum::cases()])
                ->setRequired(false)
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => InvoicePaymentTypeEnum::class]);
            yield DateField::new('payedAt')->setColumns(2);
            yield ChoiceField::new('status')->setColumns(2)
                ->setChoices([...InvoiceStatusEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => InvoiceStatusEnum::class]);
            yield AssociationField::new('period')->setColumns(2);

            yield FormField::addPanel('Days');
            yield ArrayField::new('activities')
                ->setDisabled()
                ->setLabel(false)
                ->setFormTypeOption('allow_add', false)
                ->setFormTypeOption('allow_delete', false)
                ->setColumns(6);
        }
    }

    public function validateAction(AdminContext $context): Response
    {
        /** @var Invoice $invoice */
        $invoice = $context->getEntity()->getInstance();

        $invoice->setStatus(InvoiceStatusEnum::Waiting);

        $this->entityManager->flush();

        $this->flashbagService->send('mark_as_waiting', $invoice);
        return $this->redirect($context->getReferrer());
    }

    /**
     * @throws Exception
     */
    public function payedAction(AdminContext $context): Response
    {
        /** @var Invoice $invoice */
        $invoice = $context->getEntity()->getInstance();

        $invoice->setStatus(InvoiceStatusEnum::Payed);
        $invoice->setPayedAt(new DateTime('now'));
        $this->invoiceService->updatePeriod($invoice);

        $this->entityManager->flush();

        $this->flashbagService->send('mark_as_payed', $invoice);
        return $this->redirect($context->getReferrer());
    }

    /**
     * @throws Exception
     */
    public function pdfAction(AdminContext $context): Response
    {
        /** @var Invoice $invoice */
        $invoice = $context->getEntity()->getInstance();

        return $this->generatePdf($invoice);
    }

    /**
     * @throws Exception
     * @throws TransportExceptionInterface
     */
    public function sendAction(AdminContext $context): Response
    {
        /** @var Invoice $invoice */
        $invoice = $context->getEntity()->getInstance();

        $this->generatePdf($invoice);

        $emails = $invoice->getCompany()->getEmails();

        if ($invoice->getFilename() && count($emails) > 0) {
            $email = (new Email())
                ->from($this->getParameter('MAILER_FROM'))
                ->subject($this->getParameter('MAILER_SUBJECT') . ' ' . t('Invoice') . ' n°' . $invoice->getNumber())
                ->text(
                    $this->renderView(
                        'email/invoice.txt.twig',
                        ['invoice' => $invoice]
                    )
                )
                ->attachFromPath(
                    $this->getParameter('PDF_DIRECTORY') . $invoice->getFilename(),
                    'invoice-jeremy-achain-' . $invoice->getNumber() . '.pdf'
                );

            if ($this->getParameter('APP_ENV') === 'prod') {
                $email->to($emails[0]);
                $email->addCc($this->getParameter('MAILER_FROM'));

                if (count($emails) > 1) {
                    for ($i = 1; $i < count($emails); $i++) {
                        $email->addCc($emails[$i]);
                    }
                }
            } else {
                $email->to($this->getParameter('MAILER_FROM'));
            }

            $this->mailer->send($email);
            $this->flashbagService->send('invoice_sended', $invoice);
        } else {
            $this->flashbagService->send('invoice_not_sended', $invoice, [], 'danger');
        }
        return $this->redirect($context->getReferrer());
    }

    /**
     * @throws Exception
     */
    private function generatePdf(Invoice $invoice): Response
    {
        return $this->file(
            $this->invoiceService->getOrCreatePdf($invoice, true),
            $invoice->getFilename(),
            ResponseHeaderBag::DISPOSITION_INLINE
        );
    }

    #[Route('/admin/invoice/csv', name: 'invoices_csv')]
    public function csv()
    {
        $filename = $this->invoiceService->generateInvoicesBook();

        return $this->file(
            $filename,
            'livre-recettes.csv',
            ResponseHeaderBag::DISPOSITION_ATTACHMENT
        );
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws Exception
     */
    #[Route('/admin/report/{year<\d+>}/{month<\d+>}/{slug}/invoice', name: 'report_invoice')]
    public function generate(
        int $year, int $month, Company $company
    ) {
        $currentDate = new DateTime($year . ($month < 10 ? '0' : '') . $month . '01');
        $activities = $this->activityRepository->findByCompanyAndDate($company, $currentDate);
        $invoices = $this->invoiceRepository->getByDate($currentDate);
        $invoice = new Invoice();

        if (count($invoices) == 1 && $invoices[0]->getCompany() === $company) {
            $invoice = $invoices[0];
            $invoice->importActivities($activities);
            $this->entityManager->flush();
        } else {
            $invoice = $this->invoiceService->createByActivities($currentDate, $company, $activities);
        }

        $url = $this->adminUrlGenerator
            ->setController(InvoiceCrudController::class)
            ->setAction(Action::EDIT)
            ->setEntityId($invoice->getId())
            ->generateUrl();
        return $this->redirect($url);
    }

    /**
     * @param Invoice $entityInstance
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->invoiceService->calculTotalHt($entityInstance);
        $this->invoiceService->calculTva($entityInstance);
        $this->invoiceService->updatePeriod($entityInstance);

        parent::persistEntity($entityManager, $entityInstance);
    }

    /**
     * @param Invoice $entityInstance
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws Exception
     */
    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        if ($entityInstance->isEditable()) {
            $this->invoiceService->calculTotalHt($entityInstance);
            $this->invoiceService->calculTva($entityInstance);
            $this->invoiceService->updatePeriod($entityInstance);
        }

        parent::updateEntity($entityManager, $entityInstance);
    }
}
