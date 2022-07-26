<?php

namespace App\Controller\Admin;

use App\Repository\ExperienceRepository;
use DateInterval;
use DateTime;
use Exception;
use Symfony\Component\Routing\Annotation\Route;

class TimelineController
{
    /**
     * @throws Exception
     */
    #[Route(path: '/admin/timeline', name: 'timeline')]
    public function report(ExperienceRepository $experienceRepository): void
    {
        $viewData = [
            'timeline' => [],
            'months'   => []
        ];
        $experiences = $experienceRepository->findBy(['isFreelance' => true]);
        $firstYear = 2015;
        $lastYear = intval((new DateTime())->format('Y'));
        $lastMonth = intval((new DateTime())->format('m'));
        for ($y = $firstYear; $y <= $lastYear; $y++) {
            $viewData['timeline'][$y] = [];
            for ($m = 1; $m <= 12; $m++) {
                $viewData['timeline'][$y][$m] = [];
            }
        }
        for ($m = 1; $m <= 12; $m++) {
            $viewData['months'][] = $m;
        }
        foreach ($experiences as $experience) {
            $dateBegin = $experience->getDateBegin();
            $dateEnd = $experience->getDateEnd() ?: new DateTime();
            $currentDate = clone $dateBegin;
            $yearBegin = intval($experience->getDateBegin()->format('Y'));
            $monthBegin = intval($experience->getDateBegin()->format('m'));

            do {
                $y = intval($currentDate->format('Y'));
                $m = intval($currentDate->format('m'));
                $viewData['timeline'][$y][$m][] = $experience;
                $currentDate->add(new DateInterval('P1M'));
            } while ($currentDate < $dateEnd);
        }
        //return $this->render('page/timeline.html.twig', $viewData);
    }
}