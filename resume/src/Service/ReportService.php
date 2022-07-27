<?php

namespace App\Service;

use App\Entity\Activity;
use App\Entity\Company;
use App\Repository\ActivityRepository;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;

class ReportService
{
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly InvoiceRepository      $invoiceRepository,
        private readonly ActivityRepository     $activityRepository,
        private readonly ExperienceRepository   $experienceRepository,
        private readonly TranslatorInterface    $translator,
    ) {

    }

    /**
     * Génère le tableau à partir d'un date donné
     * @param null $company
     * @throws Exception
     */
    public function generateMonth(DateTime $currentDate, array $activities, $company = null): array
    {
        $activitiesData = [];
        $now = (new DateTime())->format('Ymd');

        for ($i = 1; $i < $currentDate->format('N'); $i++) {
            $activitiesData[] = [
                'selected' => false,
                'date'     => null,
                'value'    => null
            ];
        }

        $emptyDays = $currentDate->format('t');
        for ($i = 1; $i <= $emptyDays; $i++) {
            $date = $currentDate->format('Ymd');
            $dayInWeek = intval($currentDate->format('w'));

            if ($dayInWeek !== 6 && $dayInWeek !== 0) {
                $activitiesData[$date] = [
                    'selected' => false,
                    'date'     => clone $currentDate,
                    'value'    => 1,
                    'company'  => $company,
                    'current'  => $date === $now
                ];
            }
            $currentDate->add(new DateInterval('P1D'));
        }

        foreach ($activities as $activity) {
            $date = $activity->getDate()->format('Ymd');

            if (isset($activitiesData[$date])) {
                $activitiesData[$date]['value'] = $activity->getValue();
                $activitiesData[$date]['company'] = $activity->getCompany();
                $activitiesData[$date]['selected'] = true;
            }
        }

        $count = count($activitiesData);

        for ($i = $count; $i < ceil($count / 7) * 7; $i++) {
            $activitiesData[] = [
                'selected' => false,
                'date'     => null,
                'value'    => null
            ];
        }

        return $activitiesData;
    }

    /**
     * @throws Exception
     */
    public function getDashboard(array $viewData, DateTime $currentDate, int $year, int $month, ?Company $company
    ): array {
        $viewData['daysCount'] = $currentDate->format('t');
        $viewData['years'] = $this->invoiceRepository->findYears();

        if (!in_array($viewData['activeYear'], $viewData['years'])) {
            $viewData['years'][] = $viewData['activeYear'];
        }
        if (!in_array($viewData['currentYear'], $viewData['years'])) {
            $viewData['years'][] = $viewData['currentYear'];
        }

        $viewData['months'] = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthDate = new DateTime($viewData['activeYear'] . ($i < 10 ? '0' : '') . $i . '01');
            $viewData['months'][] = [
                'int' => $i,
                'str' => $this->translator->trans($monthDate->format('F'))
            ];
        }

        $viewData['companies'] = [];
        $currentExperiences = $this->experienceRepository->getCurrents();
        foreach ($currentExperiences as $experience) {
            $viewData['companies'][] = $experience->getClient() ?: $experience->getCompany();
        }
        $viewData['activeCompany'] = (is_countable($viewData['companies']) ? count(
                $viewData['companies']
            ) : 0) == 1 ?? !$company ? $viewData['companies'][0] : $company;

        $viewData['invoices'] = $this->invoiceRepository->getByDate($currentDate);

        $viewData['companyActivities'] =
            $viewData['activeCompany']
                ? $this->activityRepository->findByCompanyAndDate($viewData['activeCompany'], $currentDate)
                : $this->activityRepository->findByDate($currentDate);

        return $viewData;
    }

    public function sendActivities(array $formData, DateTime $currentDate): void
    {
        $dayCount = 0;

        foreach ($formData['activities'] as $activityData) {
            if ($activityData['date'] && $activityData['selected']) {
                $dayCount++;
            }
        }

        $this->activityRepository->cleanByDate($currentDate);

        foreach ($formData['activities'] as $activityData) {
            if ($activityData['date'] && $activityData['selected']) {
                $activity = new Activity();
                $activity->setDate($activityData['date']);
                $activity->setValue($activityData['value']);
                $activity->setCompany($activityData['company']);

                $this->entityManager->persist($activity);
            }

        }

        $this->entityManager->flush();
    }

    /**
     * @TODO
     * Envoi d'un mail à la fin du mois si une mission est en cours pour penser à envoyer un CRA
     */
    public function getNotifications(): array
    {
        return [];
    }
}