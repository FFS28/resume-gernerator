<?php

namespace App\Service;


use App\Entity\Activity;
use DateInterval;

class ReportService
{
    /**
     * Génère le tableau à partir d'un date donné
     * @param \DateTime $currentDate
     * @param array $activities
     * @param null $company
     * @return array
     * @throws \Exception
     */
    public function generateMonth(\DateTime $currentDate, array $activities, $company = null): array
    {
        $activitiesData = [];

        for($i = 1; $i < $currentDate->format('N'); $i++) {
            $activitiesData[] = [
                'selected' => false,
                'date' => null,
                'value' => null
            ];
        }

        $emptyDays = $currentDate->format('t');
        for ($i = 1; $i <= $emptyDays; $i++) {
            $date = $currentDate->format('Ymd');

            $activitiesData[$date] = [
                'selected' => false,
                'date' => clone $currentDate,
                'value' => 1,
                'company' => $company
            ];
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

        for($i = $count; $i < ceil($count / 7) * 7; $i++)
        {
            $activitiesData[] = [
                'selected' => false,
                'date' => null,
                'value' => null
            ];
        }

        return $activitiesData;
    }

    /**
     * Envoi d'un mail à la fin du mois si une mission est en cours pour penser à envoyer un CRA
     * @return array
     */
    public function getNotifications()
    {
        $notifications = [];

        return $notifications;
    }
}