<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Period;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use function Sodium\add;

class PeriodService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PeriodRepository */
    private $periodRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        PeriodRepository $periodRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->periodRepository = $periodRepository;
    }

    /**
     * Renvoi la période courante
     * @param \DateTime $date
     * @return Period[]
     * @throws \Exception
     */
    public function getCurrentPeriod($date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        }

        $year = intval($date->format('Y'));

        $annualyPeriod = $this->periodRepository->findOneBy([
            'year' => $year - 1
        ]);
        $quarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $year - 1,
            'quarter' => ceil(intval($date->format('n')) / 3)
        ]);

        return [$annualyPeriod, $quarterlyPeriod];
    }

    /**
     * @param $year
     * @return Period|null
     */
    public function getAnnualyByYear($year)
    {
        return $this->periodRepository->findOneBy([
            'year' => $year
        ]);
    }

    /**
     * @param $year
     * @return Period[]
     */
    public function getQuarterlyByYear($year)
    {
        $quarterly = [];
        for ($quarter = 1; $quarter <= 4; $quarter++) {
            $quarterly[] = $this->periodRepository->findOneBy([
                'year' => $year,
                'quarter' => $quarter
            ]);
        }
        return $quarterly;
    }


    /**
     * Renvoi la précédente période
     * @return Period[]
     * @throws \Exception
     */
    public function getPreviousPeriod()
    {
        $date = (new \DateTime())->sub(new \DateInterval('P1M'));
        return $this->getCurrentPeriod($date);
    }
}
