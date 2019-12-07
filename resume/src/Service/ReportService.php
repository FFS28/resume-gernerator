<?php

namespace App\Service;


use App\Entity\Activity;
use DateInterval;

class ReportService
{
    /**
     * @param \DateTime $currentDate
     * @param array $activities
     * @param null $company
     * @return array
     * @throws \Exception
     */
    public function generateMonth(\DateTime $currentDate, array $activities, $company = null): array
    {
        for($i = 1; $i < $currentDate->format('N'); $i++) {
            $activitiesData[] = [
                'selected' => false,
                'date' => null,
                'value' => null
            ];
        }

        for ($i = 1; $i <= $currentDate->format('t'); $i++) {
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

        return $activitiesData;
    }
}