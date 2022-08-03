<?php

namespace App\Service;


use App\Entity\Operation;
use App\Enum\InvoiceStatusEnum;
use App\Enum\OperationTypeEnum;
use App\Repository\OperationRepository;
use App\Repository\StatementRepository;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use JetBrains\PhpStorm\ArrayShape;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;

class AccountingService
{
    public function __construct(
        private readonly OperationRepository $operationRepository,
        private readonly StatementRepository $statementRepository,
        private readonly ChartBuilderInterface $chartBuilder,
        private readonly TranslatorInterface $translator
    )
    {

    }

    /**
     * @throws \Exception
     */
    #[ArrayShape([
        'years' => "array",
        'types' => "array",
        'activeType' => "string",
        'activeYear' => "int",
        'chartTotalsByMonthAndLabel' => \Symfony\UX\Chartjs\Model\Chart::class,
        'chartTotalsByMonthAndType' => \Symfony\UX\Chartjs\Model\Chart::class
    ])]
    public function getDashboard(?int $year, ?int $month, ?string $type): array
    {
        $colors = [
            "#25CCF7","#FD7272","#54a0ff","#00d2d3",
            "#1abc9c","#2ecc71","#3498db","#9b59b6","#34495e",
            "#16a085","#27ae60","#2980b9","#8e44ad","#2c3e50",
            "#f1c40f","#e67e22","#e74c3c","#ecf0f1","#95a5a6",
            "#f39c12","#d35400","#c0392b","#bdc3c7","#7f8c8d",
            "#55efc4","#81ecec","#74b9ff","#a29bfe","#dfe6e9",
            "#00b894","#00cec9","#0984e3","#6c5ce7","#ffeaa7",
            "#fab1a0","#ff7675","#fd79a8","#fdcb6e","#e17055",
            "#d63031","#feca57","#5f27cd","#54a0ff","#01a3a4"
        ];
        $colorsByTypes = [
            OperationTypeEnum::Charge->toString() => '#18227c',
            OperationTypeEnum::Food->toString() => '#33691e',
            OperationTypeEnum::Supply->toString() => '#ff6f00',
            OperationTypeEnum::Hobby->toString() => '#fdd835',
            OperationTypeEnum::Subscription->toString() => '#ad1457',
            OperationTypeEnum::Other->toString() => '#616161',
        ];
        $years = array_column($this->operationRepository->listYears(), 'date');
        sort($years);

        $types = iterator_to_array(OperationTypeEnum::choices(), true);

        unset($types[OperationTypeEnum::Hidden->value]);
        unset($types[OperationTypeEnum::Income->value]);
        unset($types[OperationTypeEnum::Refund->value]);

        $viewData = [
            'years' => $years,
            'types' => $types,
            'activeType' => $type,
            'activeYear' => $year,
            'activeMonth' => $month,
            'months' => []
        ];


        if ($viewData['activeYear']) {
            for ($i = 1; $i <= 12; $i++) {
                $monthDate = new DateTime($viewData['activeYear'] . ($i < 10 ? '0' : '') . $i . '01');
                $viewData['months'][] = [
                    'int' => $i,
                    'str' => $this->translator->trans($monthDate->format('F'))
                ];
            }
        }

        $totalsByMonthAndType = $this->operationRepository->getTotalsByMonthAndType($year, $type);
        $totalsByMonthAndTypeStats = [];
        $emptyValuesByTypes = array_fill(0, count($types), 0);

        foreach ($totalsByMonthAndType as $row) {
            if (!isset($totalsByMonthAndTypeStats[$row['date']])) {
                $totalsByMonthAndTypeStats[$row['date']] = array_combine(array_keys($types), $emptyValuesByTypes);
            }

            $totalsByMonthAndTypeStats[$row['date']][$row['type']->value] = -floatval($row['total']);
        }

        $totalsByMonthAndTypeLabels = array_keys($totalsByMonthAndTypeStats);
        $totalsByMonthAndTypeStats = array_values($totalsByMonthAndTypeStats);

        $chartTotalsByMonthAndType = $this->chartBuilder->createChart(Chart::TYPE_BAR);
        $chartTotalsByMonthAndType->setData([
                                                'labels' => $totalsByMonthAndTypeLabels,
                                                'datasets' => [
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Charge->toString()),
                                                        'backgroundColor' => $colorsByTypes[OperationTypeEnum::Charge->toString()],
                                                        'data' => array_map(fn($item) => $item[OperationTypeEnum::Charge->value], $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Food->toString()),
                                                        'backgroundColor' => $colorsByTypes[OperationTypeEnum::Food->toString()],
                                                        'data' => array_map(fn($item) => $item[OperationTypeEnum::Food->value], $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Supply->toString()),
                                                        'backgroundColor' => $colorsByTypes[OperationTypeEnum::Supply->toString()],
                                                        'data' => array_map(fn($item) => $item[OperationTypeEnum::Supply->value], $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Hobby->toString()),
                                                        'backgroundColor' => $colorsByTypes[OperationTypeEnum::Hobby->toString()],
                                                        'data' => array_map(fn($item) => $item[OperationTypeEnum::Hobby->value], $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Subscription->toString()),
                                                        'backgroundColor' => $colorsByTypes[OperationTypeEnum::Subscription->toString()],
                                                        'data' => array_map(fn($item) => $item[OperationTypeEnum::Subscription->value], $totalsByMonthAndTypeStats),
                                                    ],
                                                    [
                                                        'label' => $this->translator->trans(OperationTypeEnum::Other->toString()),
                                                        'backgroundColor' => $colorsByTypes[OperationTypeEnum::Other->toString()],
                                                        'data' => array_map(fn($item) => $item[OperationTypeEnum::Other->value], $totalsByMonthAndTypeStats),
                                                    ],
                                                ],
                                            ]);

        $chartTotalsByMonthAndType->setOptions([
             'scales' => [
                 'x' => [
                     'stacked' => true,
                 ],
                 'y' => [
                     'stacked' => true,
                     'ticks' => [
                         'stepSize' => $type ? null : 200,
                         'lineHeight' => 1
                     ],
                     'gridLines' => [
                         'lineWidth' => 1
                     ]
                 ]
             ]
         ]);
        $viewData['chartTotalsByMonthAndType'] = $chartTotalsByMonthAndType;

        if ($type) {
            $totalsByMonthAndLabelStats = $labels = $totalsByMonthAndLabelDatasets = [];
            $totalsByMonthAndLabel = $this->operationRepository->getTotalsByMonthAndLabel($year, $type);

            foreach ($totalsByMonthAndLabel as $row) {
                $totalsByMonthAndLabelStats[$row['date']][$row['label']] = -floatval($row['total']);

                if (!in_array($row['label'], $labels)) {
                    $labels[] = $row['label'];
                }
            }

            $totalsByMonthAndLabelLabels = array_keys($totalsByMonthAndLabelStats);
            $totalsByMonthAndLabelStats = array_values($totalsByMonthAndLabelStats);

            foreach ($labels as $index => $label) {
                $totalsByMonthAndLabelDatasets[] = [
                    'label' => $label,
                    'backgroundColor' => $colors[$index],
                    'data' => array_fill(0, count($totalsByMonthAndLabelLabels), 0)
                ];
            }
            foreach ($totalsByMonthAndLabelStats as $monthIndex => $monthValue) {
                foreach ($monthValue as $label => $total) {
                    $labelIndex = array_search($label, $labels);
                    $totalsByMonthAndLabelDatasets[$labelIndex]['data'][$monthIndex] = $total;
                }
            }
            $chartTotalsByMonthAndLabel = $this->chartBuilder->createChart(Chart::TYPE_BAR);
            $chartTotalsByMonthAndLabel->setData([
                                                     'labels' => $totalsByMonthAndLabelLabels,
                                                     'datasets' => $totalsByMonthAndLabelDatasets
                                                 ]);
            $viewData['chartTotalsByMonthAndLabel'] = $chartTotalsByMonthAndLabel;
        }

        if ($year && $month) {
            $totalsByLabelStats = $labels = $totalsByLabelDatasets = $colorsByLabels = [];
            $totalsByLabel = $this->operationRepository->getTotalsByLabel($year, $month, $type);

            foreach ($totalsByLabel as $index => $row) {
                $label = $type ? $row['label'] : $row['type']->toString();
                $totalsByLabelStats[$label] = abs(floatval($row['total']));

                if (!in_array($label, $labels)) {
                    $labels[] = $type ? $label : $this->translator->trans($label);
                }
                $colorsByLabels[] = $type ? $colors[$index] : $colorsByTypes[$label];
            }

            $monthDate = new DateTime($viewData['activeYear'] . ($month < 10 ? '0' : '') . $month . '01');

            $chartTotalsByLabel = $this->chartBuilder->createChart(Chart::TYPE_BAR);
            $chartTotalsByLabel->setData([
                                                     'labels' => $labels,
                                                     'datasets' => [[
                                                         'label' => $this->translator->trans($monthDate->format('F')),
                                                         'backgroundColor' => $colorsByLabels,
                                                         'data' => array_values($totalsByLabelStats)
                                                     ]]
                                                 ]);
            $chartTotalsByLabel->setOptions([
                'indexAxis' => 'y',
                'plugins' => [
                    'datalabels' => [
                        'display' => true,
                        'anchor' => 'end',
                        'align' => 'right',
                        'font' => [
                            'weight' => 'bold'
                        ]
                    ]
                ]
            ]);
            $viewData['chartTotalsByLabel'] = $chartTotalsByLabel;
        }

        $savingAmounts = $this->statementRepository->getSavingAmounts($year);
        $labels = $values = [];

        foreach ($savingAmounts as $savingAmount) {
            $labels[] = $savingAmount['date'];
            $values[] = $savingAmount['savingAmount'];
        }
        $chartSavingAmounts = $this->chartBuilder->createChart(Chart::TYPE_LINE);
        $chartSavingAmounts->setData([
                                         'labels' => $labels,
                                         'datasets' => [[
                                                            'label' => 'Livret Bleu',
                                                            'borderColor' => 'rgba(96, 165, 250, 0.6)',
                                                            'backgroundColor' => 'rgba(96, 165, 250, 0.6)',
                                                            'data' => $values
                                                        ]]
                                     ]);
        $viewData['chartSavingAmounts'] = $chartSavingAmounts;

        return $viewData;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getNullTypesCount(): int
    {
        return $this->operationRepository->countNullTypes();
    }
}