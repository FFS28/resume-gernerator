<?php

namespace App\Service;

use App\Entity\Consumption;
use App\Entity\ConsumptionMonth;
use App\Entity\ConsumptionStatement;
use App\Helper\StringHelper;
use App\Repository\ConsumptionMonthRepository;
use App\Repository\ConsumptionRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class ConsumptionService
{
    public function __construct(
        private readonly string                     $consumptionStatementDirectory,
        private readonly EntityManagerInterface     $entityManager,
        private readonly ConsumptionRepository      $consumptionRepository,
        private readonly ConsumptionMonthRepository $consumptionMonthRepository,
        private readonly ChartBuilderInterface      $chartBuilder,
        private readonly TranslatorInterface        $translator
    ) {
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
            if (!$consumption) {
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
                        $previousConsumption->setDiffWeekendHour(
                            $weekendHour - $previousConsumption->getMeterWeekendHour()
                        );
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
                } // Si c'est le dernier
                else {
                    $nextConsumption = $this->consumptionRepository->get((clone $date)->modify('+1 day'));

                    if ($nextConsumption) {
                        $consumption->setDiffLowHour($nextConsumption->getMeterLowHour() - $lowHour);
                        $consumption->setDiffFullHour($nextConsumption->getMeterFullHour() - $fullHour);
                        $consumption->setDiffWeekendHour($nextConsumption->getMeterWeekendHour() - $weekendHour);
                    }
                }

                if ($consumption->getDiffLowHour() || $consumption->getDiffFullHour(
                    ) || $consumption->getDiffWeekendHour()) {
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
                    $consumptionMonth->getYear(), $consumptionMonth->getMonth()
                );

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

    public function get(ConsumptionStatement $consumption): string
    {
        return $this->consumptionStatementDirectory . $consumption->getFilename();
    }

    /**
     * @throws Exception
     */
    public function getDashboard(int $year, int $month, ?string $type)
    {
        $colors = [
            "#25CCF7", "#FD7272", "#54a0ff", "#00d2d3",
            "#1abc9c", "#2ecc71", "#3498db", "#9b59b6", "#34495e",
            "#16a085", "#27ae60", "#2980b9", "#8e44ad", "#2c3e50",
            "#f1c40f", "#e67e22", "#e74c3c", "#ecf0f1", "#95a5a6",
            "#f39c12", "#d35400", "#c0392b", "#bdc3c7", "#7f8c8d",
            "#55efc4", "#81ecec", "#74b9ff", "#a29bfe", "#dfe6e9",
            "#00b894", "#00cec9", "#0984e3", "#6c5ce7", "#ffeaa7",
            "#fab1a0", "#ff7675", "#fd79a8", "#fdcb6e", "#e17055",
            "#d63031", "#feca57", "#5f27cd", "#54a0ff", "#01a3a4"
        ];

        $years = array_column($this->consumptionRepository->listYears(), 'date');
        sort($years);

        $types = [
            'low'     => 'Low Hour',
            'full'    => 'Full Hour',
            'weekend' => 'Weekend Hour',
        ];
        $typeIndex = $type !== 0 ? array_search($type, array_keys($types)) : false;


        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $monthDate = new DateTime('2000' . StringHelper::addZeros($i, 2) . '01');
            $months[$i] = $this->translator->trans($monthDate->format('F'));
        }

        $viewData = [
            'years'       => $years,
            'months'      => $months,
            'types'       => $types,
            'activeType'  => $type,
            'activeYear'  => $year,
            'activeMonth' => $month,
        ];

        if (!$year) {
            // On veux comparer les consommations d'un mois d'une année sur l'autre
            $consumptionMonths = $this->consumptionMonthRepository->findAll();
            $consumptionMonthsData = [];

            foreach ($consumptionMonths as $consumptionMonth) {
                if (!isset($consumptionMonthsData[$consumptionMonth->getYear()])) {
                    $consumptionMonthsData[$consumptionMonth->getYear()] = $array = array_fill(1, 12, [
                        null, null, null, null
                    ]);
                }
                $consumptionMonthsData[$consumptionMonth->getYear()][$consumptionMonth->getMonth()] = [
                    $consumptionMonth->getLowHourMegaWatt(),
                    $consumptionMonth->getFullHourMegaWatt(),
                    $consumptionMonth->getWeekendHourMegaWatt(),
                    $consumptionMonth->getTotalMegaWatt(),
                ];
            }

            $dataSets = [];
            $i = 0;
            foreach ($consumptionMonthsData as $currentYear => $consumptionYear) {
                $data = [];
                foreach ($consumptionYear as $index => $consumptionMonth) {
                    $data[$months[$index]] = $consumptionMonth[$typeIndex !== false ? $typeIndex : 3];
                }

                $dataSets[] = [
                    'label'           => $currentYear,
                    'borderColor'     => $colors[$i],
                    'backgroundColor' => $colors[$i],
                    'data'            => $data
                ];

                $i++;
            }

            $chartTotalsByYearAndMonth = $this->chartBuilder->createChart(Chart::TYPE_LINE);
            $chartTotalsByYearAndMonth->setData([
                                                    'labels'   => array_values($months),
                                                    'datasets' => $dataSets
                                                ]);
            $viewData['chartTotalsByYearAndMonth'] = $chartTotalsByYearAndMonth;
        } else {
            if (!$month) {
                // Sur une année, on veux voir la consommation de chaque type, sur chaque mois
                $consumptionMonths = $this->consumptionMonthRepository->findBy(['year' => $year]);
                $consumptionMonthsData = array_fill(1, 12, [
                    null, null, null, null
                ]);

                foreach ($consumptionMonths as $consumptionMonth) {
                    $consumptionMonthsData[$consumptionMonth->getMonth()] = [
                        $consumptionMonth->getLowHourMegaWatt(),
                        $consumptionMonth->getFullHourMegaWatt(),
                        $consumptionMonth->getWeekendHourMegaWatt(),
                        $consumptionMonth->getTotalMegaWatt(),
                    ];
                }

                $dataSets = [];
                if (!$type) {
                    for ($i = 0; $i < 3; $i++) {
                        $data = [];
                        foreach ($consumptionMonthsData as $month) {
                            $data[] = $month[$i];
                        }

                        $dataSets[] = [
                            'label' => $this->translator->trans(array_values($types)[$i]),
                            'backgroundColor' => $colors[$i],
                            'data' => $data,
                        ];
                    }
                } else {
                    $data = [];
                    foreach ($consumptionMonthsData as $month) {
                        $data[] = $month[$typeIndex];
                    }
                    $dataSets[] = [
                        'label' => $this->translator->trans(array_values($types)[$typeIndex]),
                        'backgroundColor' => $colors[$typeIndex],
                        'data' => $data,
                    ];
                }

                $chartTotalsByMonthAndType = $this->chartBuilder->createChart(Chart::TYPE_BAR);
                $chartTotalsByMonthAndType->setData([
                    'labels' => array_values($months),
                    'datasets' => $dataSets
                ]);
                if (!$type) {
                    $chartTotalsByMonthAndType->setOptions([
                        'scales' => [
                            'x' => [
                                'stacked' => true,
                            ],
                            'y' => [
                                'stacked' => true,
                            ]
                        ]
                    ]);
                }
                $viewData['chartTotalsByMonthAndType'] = $chartTotalsByMonthAndType;
            } else {
                $consumptionDays = $this->consumptionRepository->listByYearAndMonth($year, $month);
                $consumptionDaysData = array_fill(1, 31, [
                    null, null, null, null
                ]);

                foreach ($consumptionDays as $consumptionDay) {
                    $consumptionDaysData[intval($consumptionDay->getDate()->format('d'))] = [
                        $consumptionDay->getLowHourMegaWatt(),
                        $consumptionDay->getFullHourMegaWatt(),
                        $consumptionDay->getWeekendHourMegaWatt(),
                        $consumptionDay->getTotalMegaWatt(),
                    ];
                }

                dump($consumptionDays);
                dump($consumptionDaysData);
            }
        }

        return $viewData;
    }

}