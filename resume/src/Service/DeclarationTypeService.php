<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Repository\DeclarationRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeclarationTypeService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var DeclarationRepository */
    private $declarationRepository;

    /** @var PeriodService */
    private $periodService;

    /** @var string  */
    private $type = '';

    /**
     * DeclarationTypeService constructor.
     * @param EntityManagerInterface $entityManager
     * @param DeclarationRepository $declarationRepository
     * @param PeriodService $periodService
     * @param $type
     * @param $dueSocialMonth
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        DeclarationRepository $declarationRepository,
        PeriodService $periodService,
        $type
    ) {
        $this->entityManager = $entityManager;
        $this->declarationRepository = $declarationRepository;
        $this->periodService = $periodService;
        $this->type = $type;
    }

    public function getDueMonth()
    {
        switch ($this->type) {
            case Declaration::TYPE_SOCIAL:
                return [
                    4,
                    7,
                    10,
                    1
                ];
                break;

            case Declaration::TYPE_TVA:
                return [
                    7,
                    12,
                    4
                ];
                break;

            case Declaration::TYPE_IMPOT:
                return [
                    5
                ];
                break;


            case Declaration::TYPE_CFE:
                return [
                    11
                ];
                break;
        }

        return [];
    }


    public function getTotalByYear($year)
    {
        $declarations = [];
        $amount = 0;

        $period = $this->periodService->getAnnualyByYear($year);
        $declaration = $this->declarationRepository->findOneBy([
            'type' => $this->type,
            'period' => $period
        ]);
        if ($declaration) {
            $declarations[] = $declaration;
        }

        if ($this->type === Declaration::TYPE_SOCIAL) {
            $period = $this->periodService->getQuarterlyByYear($year);
            foreach ($period as $period) {
                $declaration = $this->declarationRepository->findOneBy([
                    'type' => $this->type,
                    'period' => $period
                ]);

                if ($declaration) {
                    $declarations[] = $declaration;
                }
            }
        }

        foreach ($declarations as $declaration) {
            $amount+= $declaration->getTax();
        }

        return $amount;
    }

    /**
     * Verifie si on est dans un mois de déclaration
     * @return bool
     */
    public function isDueMonth()
    {
        $date = new \DateTime();
        $dueDatesMonth = $this->getDueMonth();

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            if (intval($date->format('m')) ===  $dueDateMonth) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retourne l'année comptable
     * @param \DateTime $date
     * @return int
     */
    public function getAccountingYear(\DateTime $date): int
    {
        $dueMonth = $this->getDueMonth();
        $lastMonth = array_pop($dueMonth);
        $year = intval($date->format('Y'));
        $month = intval($date->format('m'));

        if ($month == $lastMonth) {
            $year -= 1;
        }

        return $year;
    }

    /**
     * Récupère les dates courantes de déclarations
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getDueDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = $this->getDueMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new \DateTime($this->getAccountingYear($date).'-'.($dueDateMonth < 10 ? '0' : '').$dueDateMonth.'-01');
            if ($index == count($dueDatesMonth) - 1) {
                $dueDateBegin->add(new \DateInterval('P1Y'));
            }

            $dueDates[] = [
                $index + 1,
                clone $dueDateBegin,
                $dueDateBegin
                    ->add(new \DateInterval('P'.(intval($dueDateBegin->format('t')) - 1).'D'))
                    ->add(new \DateInterval('PT23H59M59S'))
            ];
        }

        return $dueDates;
    }

    /**
     * Retourne les dates de début et fin du prochain trimestre de cotisation
     * @param \DateTime|null $date
     * @return array
     * @throws \Exception
     */
    public function getNextDueDate(\DateTime $date = null): array
    {
        $currentDate = $date ? $date : new \DateTime();
        $dueDates = $this->getDueDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate)
        {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate >= $dueDate[1] && $currentDate <= $dueDate[2];
                $dueDate[] = $this->type;
                return $dueDate;
            }
        }

        return [];
    }

    /**
     * Récupère la déclaration sociale courante
     * @param bool $forceCurrent
     * @return Declaration|mixed
     * @throws \Exception
     */
    public function getDeclarations($forceCurrent = false)
    {
        if ($this->type === Declaration::TYPE_SOCIAL) {
            list ($annualyPeriod, $quarterlyPeriod)
                = $this->isDueMonth() && !$forceCurrent ? $this->periodService->getPreviousPeriod() : $this->periodService->getCurrentPeriod();
        } else {
            list ($annualyPeriod) = $this->periodService->getCurrentPeriod();
        }

        if ($this->type === Declaration::TYPE_SOCIAL) {
            $declaration = $this->declarationRepository->getByDate(
                $this->type,
                $quarterlyPeriod->getYear(),
                $quarterlyPeriod->getQuarter()
            );
        } else {
            $declaration = $this->declarationRepository->getByDate($this->type, $annualyPeriod->getYear());
        }

        if (!$declaration) {
            $declaration = new Declaration();
            $declaration->setType($this->type);
            $declaration->setRevenue(0);
            $declaration->setTax(0);

            if ($this->type === Declaration::TYPE_SOCIAL) {
                $declaration->setPeriod($quarterlyPeriod);
            } else {
                $declaration->setPeriod($annualyPeriod);
            }

            $this->entityManager->persist($declaration);
        }

        $this->entityManager->flush();

        return $declaration;
    }
}
