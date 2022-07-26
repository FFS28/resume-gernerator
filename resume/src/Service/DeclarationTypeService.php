<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Enum\DeclarationTypeEnum;
use App\Repository\DeclarationRepository;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DeclarationTypeService
{
    /**
     * DeclarationTypeService constructor.
     * @param EntityManagerInterface $entityManager
     * @param DeclarationRepository $declarationRepository
     * @param PeriodService $periodService
     * @param DeclarationTypeEnum $type
     */
    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly DeclarationRepository  $declarationRepository, private readonly PeriodService $periodService,
        private readonly DeclarationTypeEnum    $type
    ) {
    }

    public function getTotalByYear($year): int|string|null
    {
        $declarations = [];
        $amount = 0;

        $period = $this->periodService->getAnnualyByYear($year);
        $declaration = $this->declarationRepository->findOneBy([
                                                                   'type'   => $this->type,
                                                                   'period' => $period
                                                               ]);
        if ($declaration) {
            $declarations[] = $declaration;
        }

        if ($this->type === DeclarationTypeEnum::Social) {
            $periods = $this->periodService->getQuarterlyByYear($year);
            foreach ($periods as $period) {
                $declaration = $this->declarationRepository->findOneBy([
                                                                           'type'   => $this->type,
                                                                           'period' => $period
                                                                       ]);

                if ($declaration) {
                    $declarations[] = $declaration;
                }
            }
        }

        foreach ($declarations as $declaration) {
            $amount += $declaration->getTax();
        }

        return $amount;
    }

    /**
     * Retourne les dates de début et fin du prochain trimestre de cotisation
     * @param DateTime|null $date
     * @throws Exception
     */
    public function getNextDueDate(DateTime $date = null): array
    {
        $currentDate = $date ?: new DateTime();
        $dueDates = $this->getDueDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate) {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate >= $dueDate[1] && $currentDate <= $dueDate[2];
                $dueDate[] = $this->type;
                return $dueDate;
            }
        }

        return [];
    }

    /**
     * Récupère les dates courantes de déclarations
     * @throws Exception
     */
    public function getDueDatesBy(DateTime $date): array
    {
        $dueDatesMonth = $this->getDueMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new DateTime(
                $this->getAccountingYear($date) . '-' . ($dueDateMonth < 10 ? '0' : '') . $dueDateMonth . '-01'
            );
            if ($index == count($dueDatesMonth) - 1) {
                $dueDateBegin->add(new DateInterval('P1Y'));
            }

            $dueDates[] = [
                $index + 1,
                clone $dueDateBegin,
                $dueDateBegin
                    ->add(new DateInterval('P' . (intval($dueDateBegin->format('t')) - 1) . 'D'))
                    ->add(new DateInterval('PT23H59M59S'))
            ];
        }

        return $dueDates;
    }

    public function getDueMonth(): array
    {
        return match ($this->type) {
            DeclarationTypeEnum::Social => [
                4,
                7,
                10,
                1
            ],
            DeclarationTypeEnum::TVA => [
                6,
                11,
                3
            ],
            DeclarationTypeEnum::Impot => [
                5
            ],
            DeclarationTypeEnum::CFE => [
                11
            ],
            default => [],
        };

    }

    /**
     * Retourne l'année comptable
     */
    public function getAccountingYear(DateTime $date): int
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
     * Récupère la déclaration sociale courante
     * @throws Exception
     */
    public function getDeclarations(bool $forceCurrent = false): ?Declaration
    {
        $quarterlyPeriod = null;
        if ($this->type === DeclarationTypeEnum::Social) {
            [$annualyPeriod, $quarterlyPeriod]
                = $this->isDueMonth() && !$forceCurrent ? $this->periodService->getPreviousPeriod(
            ) : $this->periodService->getCurrentPeriod();
        } else {
            [$annualyPeriod] = $this->periodService->getCurrentPeriod();
        }

        if ($this->type === DeclarationTypeEnum::Social) {
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

            if ($this->type === DeclarationTypeEnum::Social) {
                $declaration->setPeriod($quarterlyPeriod);
            } else {
                $declaration->setPeriod($annualyPeriod);
            }

            $this->entityManager->persist($declaration);
        }

        $this->entityManager->flush();

        return $declaration;
    }

    /**
     * Verifie si on est dans un mois de déclaration
     */
    public function isDueMonth(): bool
    {
        $date = new DateTime();
        $dueDatesMonth = $this->getDueMonth();

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            if (intval($date->format('m')) === $dueDateMonth) {
                return true;
            }
        }

        return false;
    }
}
