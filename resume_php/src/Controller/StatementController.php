<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Operation;
use App\Entity\Statement;
use App\Repository\OperationRepository;
use App\Repository\StatementRepository;
use App\Service\StatementService;
use Symfony\Contracts\Translation\TranslatableInterface;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\Contracts\Translation\TranslatorInterface;

class StatementController extends EasyAdminController
{
    /**
     * @var StatementRepository
     */
    private $statementRepository;
    /**
     * @var StatementService
     */
    private $statementService;

    public function __construct(StatementRepository $statementRepository,
                                StatementService $statementService)
    {
        $this->statementRepository = $statementRepository;
        $this->statementService = $statementService;
    }

    public function ocrAction()
    {
        $id = $this->request->query->get('id');
        /** @var Statement $entity */
        $entity = $this->statementRepository->find($id);

        if ($entity) {
            $this->statementService->extractOperations($entity);
        }

        return $this->redirectToReferrer();
    }

    /**
     * @param Statement $statement
     */
    protected function persistEntity($statement)
    {
        $statement->setDate(new \DateTime());
        parent::persistEntity($statement);
        $this->postUpdate($statement);
        $this->em->persist($statement);
        $this->em->flush();
    }

    /**
     * @param Statement $statement
     */
    protected function updateEntity($statement)
    {
        parent::updateEntity($statement);
        $this->postUpdate($statement);
        $this->statementService->extractOperations($statement);
    }

    /**
     * @param Statement $statement
     */
    public function postUpdate($statement)
    {
        $matches = [];
        preg_match('#xtrait de comptes Compte \d+ \d+.. C_C EUROCOMPTE DUO CONFORT M ACHAIN JEREMY au (\d{4}-\d{2}-\d{2})#i', $statement->getFilename(), $matches);
        if (count($matches) === 2) {
            $statement->setDate(\DateTime::createFromFormat('Y-m-d', $matches[1]));
        }
    }

    /**
     * @param ChartBuilderInterface $chartBuilder
     * @param OperationRepository $operationRepository
     * @param TranslatorInterface $translator
     * @param int $year
     * @param string $type
     * @return Response
     * @Route("/admin/accounting/{year<\d+>?0}/{type<\w+>?}", name="accounting")
     */
    public function accounting(
        ChartBuilderInterface $chartBuilder,
        OperationRepository $operationRepository,
        TranslatorInterface $translator,
        int $year = 0, $type = ''
    ) {
        $viewData = [];

        $totalsByMonthAndType = $operationRepository->getTotalsByMonthAndType($year, $type);
        $totalsByMonthAndTypeStats = [];
        $emptyValuesByTypes = array_fill(0, count(Operation::TYPES), 0);

        foreach ($totalsByMonthAndType as $row) {
            if (!isset($totalsByMonthAndTypeStats[$row['date']])) {
                $totalsByMonthAndTypeStats[$row['date']] = array_combine(array_keys(Operation::TYPES), $emptyValuesByTypes);
            }

            $totalsByMonthAndTypeStats[$row['date']][$row['type']] = -floatval($row['total']);
        }

        $totalsByMonthAndTypeLabels = array_keys($totalsByMonthAndTypeStats);
        $totalsByMonthAndTypeStats = array_values($totalsByMonthAndTypeStats);

        $chartTotalsByByTypeAndMonth = $chartBuilder->createChart(Chart::TYPE_BAR);
        $chartTotalsByByTypeAndMonth->setData([
            'labels' => $totalsByMonthAndTypeLabels,
            'datasets' => [
                [
                    'label' => $translator->trans(Operation::TYPE_CHARGE),
                    'backgroundColor' => '#18227c',
                    'data' => array_map(function($item) {return $item[Operation::TYPE_CHARGE];}, $totalsByMonthAndTypeStats),
                ],
                [
                    'label' => $translator->trans(Operation::TYPE_FOOD),
                    'backgroundColor' => '#33691e',
                    'data' => array_map(function($item) {return $item[Operation::TYPE_FOOD];}, $totalsByMonthAndTypeStats),
                ],
                [
                    'label' => $translator->trans(Operation::TYPE_SUPPLY),
                    'backgroundColor' => '#ff6f00',
                    'data' => array_map(function($item) {return $item[Operation::TYPE_SUPPLY];}, $totalsByMonthAndTypeStats),
                ],
                [
                    'label' => $translator->trans(Operation::TYPE_HOBBY),
                    'backgroundColor' => '#fdd835',
                    'data' => array_map(function($item) {return $item[Operation::TYPE_HOBBY];}, $totalsByMonthAndTypeStats),
                ],
                [
                    'label' => $translator->trans(Operation::TYPE_SUBSCRIPTION),
                    'backgroundColor' => '#ad1457',
                    'data' => array_map(function($item) {return $item[Operation::TYPE_SUBSCRIPTION];}, $totalsByMonthAndTypeStats),
                ],
                [
                    'label' => $translator->trans(Operation::TYPE_OTHER),
                    'backgroundColor' => '#616161',
                    'data' => array_map(function($item) {return $item[Operation::TYPE_OTHER];}, $totalsByMonthAndTypeStats),
                ],
            ],
        ]);
        $chartTotalsByByTypeAndMonth->setOptions([
            'scales' => [
                'xAxes' => [
                    [
                        'stacked' => true,
                    ]
                ],
                'yAxes' => [
                    [
                        'stacked' => true,
                        'ticks' => [
                            'stepSize' => 200,
                            'lineHeight' => 1
                        ],
                        'gridLines' => [
                            'lineWidth' => 1
                        ]
                    ]
                ]
            ]
        ]);
        $viewData['chartTotalsByByTypeAndMonth'] = $chartTotalsByByTypeAndMonth;

        return $this->render('page/accounting.html.twig', $viewData);
    }
}
