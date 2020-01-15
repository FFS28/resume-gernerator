<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Activity;
use App\Entity\Company;
use App\Entity\Experience;
use App\Entity\Invoice;
use App\Form\Type\ActivityType;
use App\Form\Type\MonthActivitiesType;
use App\Repository\ActivityRepository;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use App\Service\InvoiceService;
use App\Service\ReportService;
use DateInterval;
use DateTime;
use Doctrine\DBAL\Exception\NotNullConstraintViolationException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ReportController extends EasyAdminController
{
    /**
     * @Route("/admin/report/{year<\d+>?0}/{month<\d+>?0}/{slug?}", name="report")
     * @param InvoiceRepository $invoiceRepository
     * @param ActivityRepository $activityRepository
     * @param ExperienceRepository $experienceRepository
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param int $year
     * @param int $month
     * @param Company|null $company
     * @return Response
     * @throws Exception
     */
    public function report(
        InvoiceRepository $invoiceRepository,
        ActivityRepository $activityRepository,
        ExperienceRepository $experienceRepository,
        TranslatorInterface $translator,
        Request $request,
        EntityManagerInterface $entityManager,
        int $year = 0, int $month = 0, Company $company = null
    )
    {
        $viewData = [];
        $viewData['activeYear'] = intval($year ? $year : (new DateTime())->format('Y'));
        $viewData['activeMonth'] = intval($month ? $month : (new DateTime())->format('m'));
        $viewData['years'] = $invoiceRepository->findYears();

        if (!in_array($viewData['activeYear'], $viewData['years'])) {
            $viewData['years'][] = $viewData['activeYear'];
        }

        $currentDate = new DateTime($viewData['activeYear'] . ($viewData['activeMonth'] < 10 ? '0' : '') . $viewData['activeMonth'] . '01');
        $viewData['daysCount'] = $currentDate->format('t');

        $viewData['months'] = [];

        for ($i = 1; $i <= 12; $i++) {
            $monthDate = new DateTime($viewData['activeYear'] . ($i < 10 ? '0' : '') . $i . '01');
            $viewData['months'][] = [
                'int' => $i,
                'str' => $translator->trans($monthDate->format('F'))
            ];
        }

        $viewData['companies'] = [];
        /** @var Experience[] $currentExperiences */
        $currentExperiences = $experienceRepository->getCurrents();
        foreach ($currentExperiences as $experience){
            $viewData['companies'][] = $experience->getClient();
        }
        $viewData['activeCompany'] = count($viewData['companies']) == 1 ?? !$company ? $viewData['companies'][0] : $company;

        $viewData['invoices'] = $invoiceRepository->getByDate($currentDate);

        $viewData['companyActivities'] =
            $viewData['activeCompany']
                ? $activityRepository->findByCompanyAndDate($viewData['activeCompany'], $currentDate)
                : $activityRepository->findByDate($currentDate);

        $form = $this->createForm(MonthActivitiesType::class, null, [
            'activities' => $viewData['companyActivities'],
            'currentDate' => clone $currentDate,
            'company' => $viewData['activeCompany']
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

            $activityRepository->cleanByDate($currentDate);

            foreach ($formData['activities'] as $activityData) {
                if ($activityData['date'] && $activityData['selected']) {
                    $activity = new Activity();
                    $activity->setDate($activityData['date']);
                    $activity->setValue($activityData['value']);
                    $activity->setCompany($activityData['company']);

                    $entityManager->persist($activity);
                }

            }

            $entityManager->flush();
            return $this->redirectToRoute('report', ['year' => $viewData['activeYear'], 'month' => $viewData['activeMonth'], 'slug' => $viewData['activeCompany']->getSlug()]);
        }

        return $this->render('page/report.html.twig', $viewData);
    }

    /**
     * @param ActivityRepository $activityRepository
     * @param Company $company
     * @param int $year
     * @param int $month
     * @return array
     * @throws Exception
     */
    private function getActivities(ActivityRepository $activityRepository, Company $company, int $year, int $month)
    {
        $currentDate = new \DateTime($year . ($month < 10 ? '0' : '') . $month . '01');
        $activities = $activityRepository->findByCompanyAndDate($company, $currentDate);

        return [$currentDate, $activities];
    }

    /**
     * @param ActivityRepository $activityRepository
     * @param InvoiceRepository $invoiceRepository
     * @param InvoiceService $invoiceService
     * @param EntityManagerInterface $entityManager
     * @param int $year
     * @param int $month
     * @param Company $company
     * @return RedirectResponse
     * @throws Exception
     * @Route("/admin/report/{year<\d+>}/{month<\d+>}/{slug}/invoice", name="report_invoice")
     */
    public function invoice(
        ActivityRepository $activityRepository,
        InvoiceRepository $invoiceRepository,
        InvoiceService $invoiceService,
        EntityManagerInterface $entityManager,
        int $year, int $month, Company $company
    )
    {
        list($currentDate, $activities) = $this->getActivities($activityRepository, $company, $year, $month);
        $invoices = $invoiceRepository->getByDate($currentDate);
        $invoice = null;

        if (count($invoices) == 1 && $invoices[0]->getCompany() === $company) {
            $invoice = $invoices[0];
            $invoice->importActivities($activities);
            $entityManager->flush();
        } else {
            //$invoice = $invoiceService->createByActivities($currentDate, $company, $activities);
        }


        return $this->redirectToRoute('easyadmin', ['entity'=> 'Invoice', 'action'=> 'edit', 'id'=> $invoice->getId()]);
    }


    /**
     * @Route("/admin/report/{year<\d+>}/{month<\d+>}/{slug}/export", name="report_export")
     * @param ActivityRepository $activityRepository
     * @param InvoiceRepository $invoiceRepository
     * @param ReportService $reportService
     * @param TranslatorInterface $translator
     * @param EntityManagerInterface $entityManager
     * @param int $year
     * @param int $month
     * @param Company $company
     * @return Response
     * @throws Exception
     */
    public function export(
        ActivityRepository $activityRepository,
        InvoiceRepository $invoiceRepository,
        ReportService $reportService,
        TranslatorInterface $translator,
        EntityManagerInterface $entityManager,
        int $year, int $month, Company $company
    )
    {
        list($currentDate, $activities) = $this->getActivities(
            $activityRepository,
            $company->getContractor() ? $company->getContractor() : $company,
            $year, $month
        );

        $viewData = [
            'company' => $company,
            'name' => $this->getParameter('COMPANY_NAME'),
            'month' => $translator->trans($currentDate->format('F')),
            'year' => $currentDate->format('Y'),
            'reportData' => $reportService->generateMonth(clone $currentDate, $activities),
            'firstWeek' => $currentDate->format('W'),
            'filename' => 'report-' . $company->getSlug() . '-' . $currentDate->format('Y') . '-' . $currentDate->format('m') . '.pdf'
        ];

        return $this->render('page/report_pdf.html.twig', $viewData);
    }
}
