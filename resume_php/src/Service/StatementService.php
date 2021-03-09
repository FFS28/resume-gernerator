<?php

namespace App\Service;


use App\Entity\Activity;
use App\Entity\Operation;
use App\Entity\Statement;
use App\Helper\StringHelper;
use App\Repository\OperationFilterRepository;
use App\Repository\OperationRepository;
use App\Repository\StatementRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Symfony\Component\HttpKernel\KernelInterface;

class StatementService
{


    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var StatementRepository */
    private $statementRepository;
    /** @var OperationRepository */
    private $operationRepository;
    /** @var OperationFilterRepository */
    private $operationFilterRepository;
    
    private $statementDirectory;

    public function __construct(
        string $statementDirectory,
        EntityManagerInterface $entityManager,
        StatementRepository $statementRepository,
        OperationRepository $operationRepository,
        OperationFilterRepository $operationFilterRepository
    ) {
        $this->entityManager = $entityManager;
        $this->statementRepository = $statementRepository;
        $this->operationRepository = $operationRepository;
        $this->operationFilterRepository = $operationFilterRepository;
        $this->statementDirectory = $statementDirectory;
    }

    public function extractOperations(Statement $statement)
    {
        $filePath = $this->statementDirectory . $statement->getFilename();
        $operations = [];
        $logPositives = $logNegatives = [];

        // Note : Impossible de diférencier les débits et crédits
        $PDFParser = new Parser();
        $pdf = $PDFParser->parseFile($filePath);
        $text = $pdf->getText();

        $lines = explode("\n", $text);
        $startAmount = $endAmount = $totalAmount = $nbOperations = 0;

        $extractDate = $extractOperations = false;

        foreach ($lines as $index => $line) {
            if ($extractOperations && $index > $extractOperations) {
                if (strpos($line, "Date\tDate valeur\tOpération\tDébit EUROS\tCrédit EUROS\t") === 0) {
                    $extractOperations = false;
                } else {
                    $operation = explode("\t", $line);
                    if (preg_match('#\d{2}/\d{2}/\d{4}#', $operation[0])) {
                        $date = \DateTime::createFromFormat('d/m/Y', $operation[0]);

                        if (!$statement->getDate()) {
                            $statement->setDate($date);
                        }

                        $operations[] = [
                            $date,
                            $operation[2],
                            StringHelper::extractAmount($operation[3])
                        ];
                    } else {
                        $operations[count($operations) - 1][1] .= ' - ' . $operation[0];
                    }
                }
            } elseif (strpos($line, 'Compte Courant JEUNE ACTIF N°') === 0
                || strpos($line, 'C/C EUROCOMPTE DUO CONFORT N°') === 0) {
                $extractOperations = $index + 1;
            } elseif (strpos($line, 'SOLDE CREDITEUR ') > -1) {
                $lineArray = explode("\t", $line);
                $amount = StringHelper::extractAmount(strpos($line, 'SOLDE CREDITEUR ')  === 0 ? $lineArray[1] : $lineArray[2]);

                if ($startAmount && !$endAmount) {
                    $endAmount = $amount;
                } elseif (!$startAmount) {
                    $startAmount = $amount;
                }
            }
        }

        $statement->setOperationsCount(count($operations));

        $positiveFilters = array_column($this->operationFilterRepository->getPositiveFilters(), 'name');
        $positiveExceptionFilters = $this->operationFilterRepository->getPositiveExceptionFilters();
        $filters = $this->operationFilterRepository->getFilters();

        foreach ($operations as $operationLine) {
            /** @var \DateTime $date */
            $date = $operationLine[0];
            $name = $operationLine[1];
            $amount = $operationLine[2];
            $label = '';
            $isPositiv = false;

            if  (StringHelper::contains($name, $positiveFilters) === true) {
                $isPositiv = true;
            } else {
                foreach ($positiveExceptionFilters as $exception) {
                    if (strpos($name, $exception['name']) > -1
                        && $date->format('d/m/Y') === $exception['date']->format('d/m/Y')
                        && $amount == floatval($exception['amount'])) {
                        $isPositiv = true;
                        break;
                    }
                }
            }

            $amount = $isPositiv ? $amount : -$amount;
            $totalAmount += $amount;

            if ($amount > 0) {
                $logPositives[] = $name . ' : ' . $amount;
            } else {
                $logNegatives[] = $name . ' : ' . $amount . ' : ' . $date->format('d/m/Y');
            }

            if (!$this->operationRepository->findDateNameAmount($date, $name, $amount)) {
                $operation = new Operation();
                $operation->setDate($date);
                $operation->setName($name);
                $operation->setAmount($amount);

                $this->analyseOperation($operation, $filters);

                $this->entityManager->persist($operation);
                $nbOperations++;
            }
        }

        if (round($startAmount + $totalAmount, 2) != round($endAmount, 2)) {
            dump($date->format('d/m/Y'));
            dump("Start : " . $startAmount, "End : " . $endAmount, "Total : " . ($startAmount + $totalAmount));
            dump("Positives");
            dump($logPositives);
            dump("Negatives");
            dump($logNegatives);
            throw new \Exception('Les comptes ne tombent pas juste');
        }

        if ($nbOperations === 0) {
            throw new \Exception('Aucune ligne ajouté');
        }

        $this->entityManager->flush();
    }

    public function analyseOperation(Operation $operation, array $filters)
    {
        if (
            preg_match('#PAIEMENT\s+(PSC|CB)\s+[\d\s]+\s+([A-Za-z\s]*)\s+-#', $operation->getName(), $matches)
            && count($matches) == 3) {
            $operation->setLocation(trim(str_replace('FR ', '', $matches[2])));
        }
        if (!$operation->getLabel()) {
            $operation->setLabel(trim(str_replace('CARTE 12946058', '', $operation->getName())));
        }

        foreach ($filters as $filter) {
            if (preg_match('#' . $filter['name'] . '#i', $operation->getName(), $matches)) {
                $operation->setType($filter['type']);
                $operation->setTarget($filter['target']);
                $operation->setLabel($filter['label']);
                break;
            }
        }
    }
}
