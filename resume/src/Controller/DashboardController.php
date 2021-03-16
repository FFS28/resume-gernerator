<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Activity;
use App\Entity\Company;
use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Form\Type\ActivityType;
use App\Form\Type\MonthActivitiesType;
use App\Repository\ActivityRepository;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use App\Service\DeclarationService;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class DashboardController extends EasyAdminController
{
    /**
     * @param InvoiceRepository $invoiceRepository
     * @param ExperienceRepository $experienceRepository
     * @param DeclarationService $declarationService
     * @param TranslatorInterface $translator
     * @param int $year
     * @param int $quarter
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @Route("/admin/dashboard/{year<\d+>?0}/{quarter<\d+>?0}", name="dashboard")
     */
    public function index(
        ChartBuilderInterface $chartBuilder,
        InvoiceRepository $invoiceRepository,
        ExperienceRepository $experienceRepository,
        DeclarationService $declarationService,
        TranslatorInterface $translator,
        int $year = 0, int $quarter = 0
    ) {
        $viewData = [];

        $viewData['currentYear'] = intval((new DateTime())->format('Y'));
        $viewData['currentQuarter'] = ceil((new DateTime())->format('n') / 3);
        $viewData['activeYear'] = $year ? $year : $viewData['currentYear'];
        $viewData['activeQuarter'] = $quarter ? $quarter : $viewData['currentQuarter'];
        $viewData['years'] = array_filter($invoiceRepository->findYears());

        if (!in_array($viewData['currentYear'], $viewData['years'])) {
            $viewData['years'][] = $viewData['currentYear'];
        }

        $viewData['activeRevenuesOnYear'] = $invoiceRepository->getSalesRevenuesBy($viewData['activeYear']);
        $viewData['activeRevenuesOnQuarter'] = $invoiceRepository->getSalesRevenuesBy($viewData['activeYear'], $viewData['activeQuarter']);
        $viewData['currentRevenuesOnYear'] = $invoiceRepository->getSalesRevenuesBy($viewData['currentYear']);
        $viewData['currentRevenuesOnQuarter'] = $invoiceRepository->getSalesRevenuesBy($viewData['currentYear'], $viewData['currentQuarter']);
        $viewData['dayCount'] = $invoiceRepository->getDaysCountByYear($viewData['activeYear']);

        $viewData['remainingDaysBeforeTaxLimit'] = $invoiceRepository->remainingDaysBeforeTaxLimit();
        $viewData['remainingDaysBeforeLimit'] = $invoiceRepository->remainingDaysBeforeLimit();
        $viewData['currentTaxesOnQuarter'] = $invoiceRepository->getSalesTaxesBy($viewData['activeYear'], $viewData['activeQuarter']);

        $revenuesByYears = $invoiceRepository->getSalesRevenuesGroupBy('year');
        $viewData['revenuesByYears'] = array_combine(
            array_map(function($item) {return $item['year'];}, $revenuesByYears),
            array_map(function($item) {return intval($item['total']);}, $revenuesByYears)
        );

        $revenuesByQuarters = $invoiceRepository->getSalesRevenuesGroupBy('quarter', $viewData['activeYear'], null, true);
        $viewData['revenuesByQuarters'] = array_combine(
            array_map(function($item) {return 'T'.$item['quarter'];}, $revenuesByQuarters),
            array_map(function($item) {return intval($item['total']);}, $revenuesByQuarters)
        );

        $viewData['daysByMonth'] = [];
        $daysByMonth = $invoiceRepository->getDaysCountByMonth($viewData['activeYear']);
        $daysByMonthAssociative = [];
        foreach ($daysByMonth as $item) {
            $daysByMonthAssociative[intval($item['month'])] = $item['total'];
        }
        for($i = 1; $i <= 12; $i++) {
            $monthName = $translator->trans(date('F', mktime(0, 0, 0, $i, 10)));
            $viewData['daysByMonth'][$monthName] =  isset($daysByMonthAssociative[$i]) ? $daysByMonthAssociative[$i] : 0;
        }

        $viewData['colorsByYears'] = [];
        foreach ($viewData['years'] as $year) {
            $viewData['colorsByYears'][] = $year == $viewData['activeYear'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(56, 142, 60, 0.3)';
        }

        $viewData['colorsByQuarters'] = [];
        foreach ($viewData['revenuesByQuarters'] as $quarter => $item) {
            if (isset($quarter[1])) {
                $viewData['colorsByQuarters'][] = $quarter[1] == $viewData['activeQuarter'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(0, 0, 0, 0.1)';
            }
        }

        $viewData['unpayedInvoices'] = $invoiceRepository->findInvoicesBy(null, null, false);
        $viewData['currentExperiences'] = $experienceRepository->getCurrents();
        $viewData['nextDueDate'] = $declarationService->getNextDueDate();

        $viewData['globalByYears'] = [];
        foreach ($viewData['years'] as $year) {
            $totalSocial = $declarationService->declarationTypeSocial->getTotalByYear($year);
            $totalCfe = $declarationService->declarationTypeCfe->getTotalByYear($year);
            $totalTva = $declarationService->declarationTypeTva->getTotalByYear($year);
            $totalImpot = $declarationService->declarationTypeImpot->getTotalByYear($year);
            $totalSales = $invoiceRepository->getSalesRevenuesBy($year);
            $daysByMonth = $invoiceRepository->getDaysCountByYear($year);
            $net = $totalSales - $totalSocial - $totalImpot - $totalCfe;

            $viewData['globalByYears'][] = [
                'year' => $year,
                'social' => round($totalSocial),
                'cfe' => round($totalCfe),
                'tva' => round($totalTva),
                'impot' => round($totalImpot),
                'ht' => round($totalSales),
                'net' => round($net),
                'days' => $daysByMonth,
                'percent' => round($daysByMonth * 100 / (20 * 12)),
                'netByMonth' => round($net / 12),
            ];
        }

        $chartRevenuesByYears = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartRevenuesByYears->setData([
            'labels' => array_keys($viewData['revenuesByYears']),
            'datasets' => [
                [
                    'label' => 'CA (€)',
                    'backgroundColor' => array_values($viewData['colorsByYears']),
                    'data' => array_values($viewData['revenuesByYears']),
                ],
            ],
        ]);
        $viewData['chartRevenuesByYears'] = $chartRevenuesByYears;

        $chartRevenuesByQuarters = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartRevenuesByQuarters->setData([
            'labels' => array_keys($viewData['revenuesByQuarters']),
            'datasets' => [
                [
                    'label' => 'CA (€)',
                    'backgroundColor' => array_values($viewData['colorsByQuarters']),
                    'data' => array_values($viewData['revenuesByQuarters']),
                ],
            ],
        ]);
        $viewData['chartRevenuesByQuarters'] = $chartRevenuesByQuarters;

        $chartDaysByMonth = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartDaysByMonth->setData([
            'labels' => array_keys($viewData['daysByMonth']),
            'datasets' => [
                [
                    'label' => 'CA (€)',
                    'data' => array_values($viewData['daysByMonth']),
                ],
            ],
        ]);
        $viewData['chartDaysByMonth'] = $chartDaysByMonth;

        return $this->render('page/dashboard.html.twig', $viewData);
    }
}
