<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Activity;
use App\Entity\Invoice;
use App\Form\Type\ActivityType;
use App\Form\Type\MonthActivitiesType;
use App\Repository\ActivityRepository;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
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

class DashboardController extends EasyAdminController
{
    /**
     * @param int $year
     * @param int $quarter
     * @param InvoiceRepository $invoiceRepository
     * @param ExperienceRepository $experienceRepository
     * @param TranslatorInterface $translator
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @Route("/admin/dashboard/{year<\d+>?0}/{quarter<\d+>?0}", name="dashboard")
     */
    public function index(
        InvoiceRepository $invoiceRepository,
        ExperienceRepository $experienceRepository,
        TranslatorInterface $translator,
        int $year = 0, int $quarter = 0
    ) {
        $viewData = [];

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

        $viewData['currentYear'] = intval((new DateTime())->format('Y'));
        $viewData['currentQuarter'] = ceil((new DateTime())->format('n') / 3);
        $viewData['activeYear'] = $year ? $year : $viewData['currentYear'];
        $viewData['activeQuarter'] = $quarter ? $quarter : $viewData['currentQuarter'];
        $viewData['years'] = $invoiceRepository->findYears();

        $viewData['activeRevenuesOnYear'] = $invoiceRepository->getSalesRevenuesBy($viewData['activeYear']);
        $viewData['activeRevenuesOnQuarter'] = $invoiceRepository->getSalesRevenuesBy($viewData['activeYear'], $viewData['activeQuarter']);
        $viewData['currentRevenuesOnYear'] = $invoiceRepository->getSalesRevenuesBy($viewData['currentYear']);
        $viewData['currentRevenuesOnQuarter'] = $invoiceRepository->getSalesRevenuesBy($viewData['currentYear'], $viewData['currentQuarter']);
        $viewData['dayCount'] = $invoiceRepository->getDaysCountByYear($viewData['activeYear']);

        $viewData['remainingDaysBeforeTvaLimit'] = $invoiceRepository->remainingDaysBeforeTvaLimit();
        $viewData['remainingDaysBeforeLimit'] = $invoiceRepository->remainingDaysBeforeLimit();
        $viewData['currentTaxesOnQuarter'] = $invoiceRepository->getSalesTaxesBy($viewData['activeYear'], $viewData['activeQuarter']);

        $revenuesByYears = $invoiceRepository->getSalesRevenuesGroupBy('year');
        $viewData['revenuesByYears'] = array_combine(
            array_map(function($item) {return $item['year'];}, $revenuesByYears),
            array_map(function($item) {return intval($item['total']);}, $revenuesByYears)
        );

        $revenuesByQuarters = $invoiceRepository->getSalesRevenuesGroupBy('quarter', $viewData['activeYear']);
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
            $viewData['colorsByYears'][] = $year == $viewData['activeYear'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(0, 0, 0, 0.1)';
        }

        $viewData['colorsByQuarters'] = [];
        foreach ($viewData['revenuesByQuarters'] as $quarter => $item) {
            $viewData['colorsByQuarters'][] = $quarter[1] == $viewData['activeQuarter'] ? 'rgba(56, 142, 60, 0.6)' : 'rgba(0, 0, 0, 0.1)';
        }

        $viewData['unpayedInvoices'] = $invoiceRepository->findInvoicesBy(null, null, false);
        $viewData['currentExperiences'] = $experienceRepository->getCurrents();

        return $this->render('page/dashboard.html.twig', $viewData);
    }

    /**
     * @param InvoiceRepository $invoiceRepository
     * @param ActivityRepository $activityRepository
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param EntityManager $entityManager
     * @param int $year
     * @param int $month
     * @return Response
     * @throws Exception
     * @Route("/admin/report/{year<\d+>?0}/{month<\d+>?0}", name="report")
     */
    public function report(
        InvoiceRepository $invoiceRepository,
        ActivityRepository $activityRepository,
        TranslatorInterface $translator,
        Request $request,
        EntityManagerInterface $entityManager,
        int $year = 0, int $month = 0
    ) {
        $viewData = [];
        $viewData['activeYear'] = $year ? $year : (new DateTime())->format('Y');
        $viewData['activeMonth'] = $month ? $month : (new DateTime())->format('m');
        $viewData['years'] = $invoiceRepository->findYears();

        $currentDate = new DateTime($viewData['activeYear'].($viewData['activeMonth'] < 10 ? '0' : '').$viewData['activeMonth'].'01');
        $viewData['daysCount'] = $currentDate->format('t');

        $viewData['months'] = [];

        for($i = 1; $i <= 12; $i++) {
            $monthDate = new DateTime($viewData['activeYear'].($i < 10 ? '0' : '').$i.'01');
            $viewData['months'][] = [
              'int' => $i,
              'str' => $translator->trans($monthDate->format('F'))
            ];
        }

        $activities = $activityRepository->findActivitiesByDate($currentDate);

        $form = $this->createForm(MonthActivitiesType::class, null, [
            'activities' => $activities,
            'currentDate' => clone $currentDate
        ]);
        $form->handleRequest($request);
        $viewData['reportForm'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();
            $dayCount = 0;

            foreach ($formData['activities'] as $activityData) {
                if ($activityData['date'] && $activityData['selected']) {
                    $dayCount++;
                }
            }

            if (!$formData['invoice']) {
                $number = $invoiceRepository->getNewInvoiceNumber($currentDate);

                $invoice = new Invoice();
                $invoice->setNumber($number);

                $entityManager->persist($invoice);
                $formData['invoice'] = $invoice;
            }

            $activityRepository->cleanByDateAndInvoice($formData['invoice'], $currentDate);

            foreach ($formData['activities'] as $activityData) {
                if ($activityData['date'] && $activityData['selected']) {
                    $activity = new Activity();
                    $activity->setDate($activity['date']);
                    $activity->setValue($activity['value']);
                    $activity->setInvoice($formData['invoice']);

                    $entityManager->persist($activity);
                }

            }

            dump($formData);
            //$entityManager->flush();
        }

        return $this->render('page/report.html.twig', $viewData);
    }
}
