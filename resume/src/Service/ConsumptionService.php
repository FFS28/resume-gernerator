<?php

namespace App\Service;

use App\Entity\Consumption;
use App\Entity\ConsumptionMonth;
use App\Entity\ConsumptionStatement;
use App\Repository\ConsumptionRepository;
use App\Repository\ConsumptionMonthRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Hoa\Iterator\Map;

class ConsumptionService
{
    public function __construct(
        private readonly string                     $consumptionStatementDirectory,
        private readonly EntityManagerInterface     $entityManager,
        private readonly ConsumptionRepository      $consumptionRepository,
        private readonly ConsumptionMonthRepository $consumptionMonthRepository,
    ) {
    }

    public function get(ConsumptionStatement $consumption): string
    {
        return $this->consumptionStatementDirectory . $consumption->getFilename();
    }

    /**
     * @throws Exception
     */
    public function extractConsumptions(ConsumptionStatement $consumptionStatement): int
    {
        $filePath = $this->get($consumptionStatement);
        $matches = [];
        $count = 0;

        preg_match('/Enedis_Conso_Jour_(\d{8})-(\d{8})_\d+/', $consumptionStatement->getFilename(), $matches);
        list(, $dateStart, $dateEnd) = $matches;

        $consumptionStatement->setStartDate(new DateTime($dateStart));
        $consumptionStatement->setEndDate(new DateTime($dateEnd));

        $rows = [];
        if (($handle = fopen($filePath, "r")) !== FALSE) {
            $i = 1;
            while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                if ($i >= 4 && count($row) === 17) {
                    list($dateStr, , $lowHourStr, $fullHourStr, $weekendHourStr) = $row;

                    if ($lowHourStr && $fullHourStr && $weekendHourStr) {
                        $rows[] = [
                            new DateTime($dateStr),
                            intval($lowHourStr),
                            intval($fullHourStr),
                            intval($weekendHourStr)
                        ];
                    }
                }
                $i++;
            }
        }

        $consumptionMonthStore = new \Ds\Map();

        foreach ($rows as $index => $row) {
            /** @var DateTime $date */
            list($date, $lowHour, $fullHour, $weekendHour) = $row;

            $year = intval($date->format('Y'));
            $month = intval($date->format('m'));
            $monthKey = $date->format('Ym');


            $consumptionMonth = $consumptionMonthStore->hasKey($monthKey)
                ? $consumptionMonthStore->get($monthKey)
                : null;

            if (!$consumptionMonth) {
                $consumptionMonth = $this->consumptionMonthRepository->get($year, $month);
                if (!$consumptionMonth) {
                    $consumptionMonth = new ConsumptionMonth();
                    $consumptionMonth->setYear($year);
                    $consumptionMonth->setMonth($month);

                    $this->consumptionMonthRepository->add($consumptionMonth);
                }
                $consumptionMonthStore->put($monthKey, $consumptionMonth);
            }

            $consumption = $this->consumptionRepository->get($date);
            if(!$consumption) {
                $consumption = new Consumption();
                $consumption->setDate($date);
                $consumption->setConsumptionMonth($consumptionMonth);
                $consumption->setMeterLowHour($lowHour);
                $consumption->setMeterFullHour($fullHour);
                $consumption->setMeterWeekendHour($weekendHour);

                // Si c'est le premier, on met d'abord à jour le précédent
                if ($index === 0) {
                    $previousConsumption = $this->consumptionRepository->get((clone $date)->modify('-1 day'));

                    if ($previousConsumption) {
                        $previousConsumption->setDiffLowHour($lowHour - $previousConsumption->getMeterLowHour());
                        $previousConsumption->setDiffFullHour($fullHour - $previousConsumption->getMeterFullHour());
                        $previousConsumption->setDiffWeekendHour($weekendHour - $previousConsumption->getMeterWeekendHour());
                        $previousConsumption->setDiffTotal(
                            $previousConsumption->getDiffLowHour() +
                            $previousConsumption->getDiffFullHour() +
                            $previousConsumption->getDiffWeekendHour()
                        );
                    }
                }

                // Si ce n'est pas le dernier
                if ($index < (count($rows) - 1)) {
                    list(, $nextLowHour, $nextFullHour, $nextWeekendHour) = $rows[$index + 1];

                    $consumption->setDiffLowHour($nextLowHour - $lowHour);
                    $consumption->setDiffFullHour($nextFullHour - $fullHour);
                    $consumption->setDiffWeekendHour($nextWeekendHour - $weekendHour);
                }
                // Si c'est le dernier
                else {
                    $nextConsumption = $this->consumptionRepository->get((clone $date)->modify('+1 day'));

                    if ($nextConsumption) {
                        $consumption->setDiffLowHour($nextConsumption->getMeterLowHour() - $lowHour);
                        $consumption->setDiffFullHour($nextConsumption->getMeterFullHour() - $fullHour);
                        $consumption->setDiffWeekendHour($nextConsumption->getMeterWeekendHour() - $weekendHour);
                    }
                }

                if ($consumption->getDiffLowHour() || $consumption->getDiffFullHour() || $consumption->getDiffWeekendHour()) {
                    $consumption->setDiffTotal(
                        $consumption->getDiffLowHour() +
                        $consumption->getDiffFullHour() +
                        $consumption->getDiffWeekendHour()
                    );
                }

                $this->consumptionRepository->add($consumption);
                $count++;
            }
        }

        $this->entityManager->flush();
        $this->entityManager->clear();

        // On recalcule les totaux
        $consumptionMonthStore->map(
            function ($key, ConsumptionMonth $consumptionMonth) {
                $currentConsumptionMonth = $this->consumptionMonthRepository->get(
                    $consumptionMonth->getYear(), $consumptionMonth->getMonth());

                if ($currentConsumptionMonth) {
                    $consumptions = $currentConsumptionMonth->getConsumptions();

                    $totalLowHour = $totalFullHour = $totalWeekendHour = $total = 0;

                    /** @var Consumption $consumption */
                    foreach ($consumptions as $consumption) {
                        if ($consumption->getDiffTotal()) {
                            $totalLowHour += $consumption->getDiffLowHour();
                            $totalFullHour += $consumption->getDiffFullHour();
                            $totalWeekendHour += $consumption->getDiffWeekendHour();
                            $totalWeekendHour += $consumption->getDiffWeekendHour();
                            $total += $consumption->getDiffTotal();
                        }
                    }

                    $currentConsumptionMonth->setLowHour($totalLowHour);
                    $currentConsumptionMonth->setFullHour($totalFullHour);
                    $currentConsumptionMonth->setWeekendHour($totalWeekendHour);
                    $currentConsumptionMonth->setTotal($total);
                }
            }
        );

        $this->entityManager->flush();

        return $count;
    }
}