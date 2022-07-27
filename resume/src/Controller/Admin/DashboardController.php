<?php

namespace App\Controller\Admin;

use App\Entity\Attribute;
use App\Entity\Company;
use App\Entity\Declaration;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Invoice;
use App\Entity\Link;
use App\Entity\Operation;
use App\Entity\OperationFilter;
use App\Entity\Person;
use App\Entity\Skill;
use App\Entity\Statement;
use App\Entity\User;
use App\Form\Type\MonthActivitiesType;
use App\Service\AccountingService;
use App\Service\DashboardService;
use App\Service\InvoiceService;
use App\Service\ReportService;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\UserMenu;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class DashboardController extends AbstractDashboardController
{
    public function __construct(
        private readonly DashboardService  $dashboardService,
        private readonly ReportService     $reportService,
        private readonly AccountingService $accountingService,
        private readonly InvoiceService    $invoiceService
    ) {
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    #[Route('/admin/{year<\d+>?0}', name: 'dashboard')]
    public function dashboard(int $year = 0): Response
    {
        $viewData = $this->dashboardService->getDashboard($year);

        return $this->render('admin/dashboard.html.twig', $viewData);
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     * @throws Exception
     */
    #[Route('/admin/report/{year<\d+>?0}/{month<\d+>?0}/{slug?}', name: 'report')]
    public function report(Request $request, int $year = 0, int $month = 0, Company $company = null): Response
    {
        $viewData = [];
        $viewData['currentYear'] = (new DateTime())->format('Y');
        $viewData['activeYear'] = intval($year ?: $viewData['currentYear']);
        $viewData['activeMonth'] = intval($month ?: (new DateTime())->format('m'));

        $currentDate = new DateTime(
            $viewData['activeYear'] . ($viewData['activeMonth'] < 10 ? '0' : '') . $viewData['activeMonth'] . '01'
        );
        $viewData = $this->reportService->getDashboard($viewData, $currentDate, $year, $month, $company);

        $form = $this->createForm(MonthActivitiesType::class, null, [
            'activities'  => $viewData['companyActivities'],
            'currentDate' => clone $currentDate,
            'company'     => $viewData['activeCompany']
        ]);
        $form->handleRequest($request);
        $viewData['reportForm'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $this->reportService->sendActivities($form->getData(), $currentDate);
            return $this->redirectToRoute(
                'report',
                ['year' => $viewData['activeYear'], 'month' => $viewData['activeMonth'], 'slug' => $viewData['activeCompany'] ? $viewData['activeCompany']->getSlug(
                ) : '']
            );
        }

        return $this->render('admin/report.html.twig', $viewData);
    }

    #[Route('/admin/accounting/{year<\d+>?0}/{type<\w+>?}', name: 'accounting')]
    public function accouting(int $year = 0, $type = ''): Response
    {
        $viewData = $this->accountingService->getDashboard($year, $type);
        return $this->render('admin/accounting.html.twig', $viewData);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Admin')
            ->setTranslationDomain('messages')
            ->renderSidebarMinimized()
            ->renderContentMaximized();
    }

    public function configureCrud(): Crud
    {
        return parent::configureCrud()
            ->setFormThemes(['fields/form.html.twig', '@EasyAdmin/crud/form_theme.html.twig'])
            ->showEntityActionsInlined();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToUrl('Return to website', 'fa fa-arrow-left', '/');
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-chart-bar');

        yield MenuItem::section('Invoicing');
        yield MenuItem::linkToRoute('Report', 'fa fa-calendar-alt', 'report');
        yield MenuItem::linkToCrud('Invoices', 'fa fa-coins', Invoice::class)
            ->setBadge($this->invoiceService->countWaitingInvoices());
        yield MenuItem::linkToCrud('Declarations', 'fa fa-landmark', Declaration::class);
        yield MenuItem::linkToCrud('Companies', 'fa fa-building', Company::class);
        yield MenuItem::linkToCrud('Persons', 'fa fa-users', Person::class);

        yield MenuItem::section('Resume');
        yield MenuItem::linkToCrud('Experiences', 'fa fa-map-marker-alt', Experience::class);
        yield MenuItem::linkToCrud('Skills', 'fa fa-fill-drip', Skill::class);
        yield MenuItem::linkToCrud('Attributes', 'fa fa-address-card', Attribute::class);
        yield MenuItem::linkToCrud('Hobbies', 'fa fa-chess', Hobby::class);
        yield MenuItem::linkToCrud('Educations', 'fa fa-graduation-cap', Education::class);
        yield MenuItem::linkToCrud('Links', 'fa fa-link', Link::class);

        yield MenuItem::section('Other');

        yield MenuItem::section('Accounting');

        yield MenuItem::linkToRoute('Dashboard', 'fa fa-chart-pie', 'accounting');
        yield MenuItem::linkToCrud('Statements', 'fa fa-file-alt', Statement::class);
        yield MenuItem::linkToCrud('Operations', 'fa fa-columns', Operation::class)
            ->setBadge($this->accountingService->getNullTypesCount());
        yield MenuItem::linkToCrud('Filters', 'fa fa-filter', OperationFilter::class);
    }

    public function configureActions(): Actions
    {
        $editAction = Action::new(Action::EDIT, 'Edit', 'fa fa-pencil')
            ->linkToCrudAction(Action::EDIT);

        $actionDelete = Action::new(Action::DELETE, 'Delete', 'fa fa-trash-can')
            ->linkToCrudAction(Action::DELETE);

        return parent::configureActions()
            ->add(Crud::PAGE_NEW, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::INDEX)
            ->add(Crud::PAGE_EDIT, Action::DELETE)

            ->remove(Crud::PAGE_INDEX, Action::EDIT)
            ->add(Crud::PAGE_INDEX, $editAction)

            ->remove(Crud::PAGE_INDEX, Action::DELETE)
            ->add(Crud::PAGE_INDEX, $actionDelete)

            ->disable(Action::BATCH_DELETE);
    }

    /**
     * @param User $user
     */
    public function configureUserMenu(UserInterface $user): UserMenu
    {
        // Usually it's better to call the parent method because that gives you a
        // user menu with some menu items already created ("sign out", "exit impersonation", etc.)
        // if you prefer to create the user menu from scratch, use: return UserMenu::new()->...
        return parent::configureUserMenu($user)
            // use the given $user object to get the user name
            ->setName($user->getUserIdentifier())
            // use this method if you don't want to display the name of the user
            ->displayUserName()
            // use this method if you don't want to display the user image
            ->displayUserAvatar()
            // you can also pass an email address to use gravatar's service
            ->setGravatarEmail($user->getEmail());
    }

    public function configureAssets(): Assets
    {
        return Assets::new()
            ->addCssFile('build/css/admin.css');
    }
}
