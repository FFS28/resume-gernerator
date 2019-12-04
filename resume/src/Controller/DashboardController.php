<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Invoice;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class DashboardController extends EasyAdminController
{
    /**
     * @Route("/admin/dashboard/{year<\d+>?0}/{quarter<\d+>?0}", name="dashboard")
     */
    public function index(
        int $year = 0, int $quarter = 0,
        InvoiceRepository $invoiceRepository,
        ExperienceRepository $experienceRepository,
        TranslatorInterface $translator
    ) {
        $data = [];

        /**
         * Informations
         *
         * - Chiffre d'affaire de l'année
         * - Chiffre d'affaire du trimestre sur l'année
         *
         * Alertes
         *
         * - Combien de jours puis-je facturer avant de dépasser le plafond de TVA ?
         * - Combien de jours puis-je facturer avant le plafond final ?
         *
         * Statistiques
         *
         * - Chiffre d'affaire par année
         * - Chiffre d'affaire par trimestre sur l'année
         * - Nombre de jours par mois sur l'année
         *
         * Listes
         *
         * - Factures en cours
         * - Expérience en cours
         * - Clients en cours
         */

        $data['currentYear'] = intval((new \DateTime())->format('Y'));
        $data['currentQuarter'] = ceil((new \DateTime())->format('n') / 3);
        $data['activeYear'] = $year ? $year : $data['currentYear'];
        $data['activeQuarter'] = $quarter ? $quarter : $data['currentQuarter'];
        $data['years'] = $invoiceRepository->findYears();

        $data['activeRevenuesOnYear'] = $invoiceRepository->getSalesRevenuesBy($data['activeYear']);
        $data['activeRevenuesOnQuarter'] = $invoiceRepository->getSalesRevenuesBy($data['activeYear'], $data['activeQuarter']);
        $data['currentRevenuesOnYear'] = $invoiceRepository->getSalesRevenuesBy($data['currentYear']);
        $data['currentRevenuesOnQuarter'] = $invoiceRepository->getSalesRevenuesBy($data['currentYear'], $data['currentQuarter']);

        $data['remainingDaysBeforeTvaLimit'] = $invoiceRepository->remainingDaysBeforeTvaLimit();
        $data['remainingDaysBeforeLimit'] = $invoiceRepository->remainingDaysBeforeLimit();
        $data['currentTaxesOnQuarter'] = $invoiceRepository->getSalesTaxesBy($data['activeYear'], $data['activeQuarter']);

        $revenuesByYears = $invoiceRepository->getSalesRevenuesGroupBy('year');
        $data['revenuesByYears'] = array_combine(
            array_map(function($item) {return $item['year'];}, $revenuesByYears),
            array_map(function($item) {return intval($item['total']);}, $revenuesByYears)
        );

        $revenuesByQuarters = $invoiceRepository->getSalesRevenuesGroupBy('quarter', $data['activeYear']);
        $data['revenuesByQuarters'] = array_combine(
            array_map(function($item) {return 'T'.$item['quarter'];}, $revenuesByQuarters),
            array_map(function($item) {return intval($item['total']);}, $revenuesByQuarters)
        );

        $data['daysByMonth'] = [];
        $daysByMonth = $invoiceRepository->getDaysCountByMonth($data['activeYear']);
        $daysByMonthAssociative = [];
        foreach ($daysByMonth as $item) {
            $daysByMonthAssociative[intval($item['month'])] = $item['total'];
        }
        for($i = 1; $i <= 12; $i++) {
            $monthName = $translator->trans(date('F', mktime(0, 0, 0, $i, 10)));
            $data['daysByMonth'][$monthName] =  isset($daysByMonthAssociative[$i]) ? $daysByMonthAssociative[$i] : 0;
        }

        $data['colorsByYears'] = [];
        foreach ($data['years'] as $year) {
            $data['colorsByYears'][] = $year == $data['activeYear'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(0, 0, 0, 0.1)';
        }

        $data['colorsByQuarters'] = [];
        foreach ($data['revenuesByQuarters'] as $quarter => $item) {
            $data['colorsByQuarters'][] = $quarter[1] == $data['activeQuarter'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(0, 0, 0, 0.1)';
        }

        $data['unpayedInvoices'] = $invoiceRepository->findInvoicesBy(null, null, false);
        $data['currentExperiences'] = $experienceRepository->getCurrents();

        return $this->render('page/dashboard.html.twig', $data);
    }

    /**
     * @Route("/admin/report/{year<\d+>?0}/{month<\d+>?0}", name="report")
     */
    public function report(
        int $year = 0, int $month = 0,
        InvoiceRepository $invoiceRepository
    ) {
        $data = [];
        $data['activeYear'] = $year ? $year : (new \DateTime())->format('Y');
        $data['activeMonth'] = $month ? $month : (new \DateTime())->format('m');
        $data['years'] = $invoiceRepository->findYears();

        $currentDate = new \DateTime($data['activeYear'].($data['activeMonth'] < 10 ? '0' : '').$data['activeMonth'].'01');
        $data['daysCount'] = $currentDate->format('t');

        $data['days'] = [];
        for ($i = 1; $i <= $data['daysCount']; $i++) {
            $data['days'][] = [

            ];
        }

        return $this->render('page/report.html.twig', $data);
    }
}
