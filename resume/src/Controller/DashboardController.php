<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Invoice;
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
    public function index(InvoiceRepository $invoiceRepository, int $year = 0, int $quarter = 0, TranslatorInterface $translator)
    {
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
        $data['activeYear'] = $year ? $year : (new \DateTime())->format('Y');
        $data['activeQuarter'] = $quarter ? $quarter : ceil((new \DateTime())->format('n') / 3);
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
        foreach ($daysByMonth as $monthNumber => $monthValue) {
            $monthName = $translator->trans(date('F', mktime(0, 0, 0, $monthNumber, 10)));
            $data['daysByMonth'][$monthName] =  $monthValue['total'];
        }

        $data['unpayedInvoices'] = $invoiceRepository->findInvoicesBy(0, 0, false);

        return $this->render('page/dashboard.html.twig', $data);
    }
}
