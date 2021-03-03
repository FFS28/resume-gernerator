<?php

namespace App\Service;


use App\Entity\Activity;
use App\Entity\Operation;
use App\Entity\Statement;
use App\Helper\StringHelper;
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
    
    private $statementDirectory;

    public function __construct(
        string $statementDirectory,
        EntityManagerInterface $entityManager,
        StatementRepository $statementRepository,
        OperationRepository $operationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->statementRepository = $statementRepository;
        $this->operationRepository = $operationRepository;
        $this->statementDirectory = $statementDirectory;
    }

    public function extractOperations(Statement $statement)
    {
        $filePath = $this->statementDirectory . $statement->getFilename();
        $operations = [];

        $positivLabels = [
            "VIR DE M JEREMY ACHAIN",
            "VIR M JEREMY ACHAI",
            "F RETRO EUC.CONFORT DUO",
            "VIR MUTUELLES DU SOLEIL LIVR",
            "VIR CPMS",
            "VIR WEMIND / CPMS",
            "VIR CPCAM RHONE",
            "REJ FREELANCE     COMPTE SOLDE",
            "SALAIRE",
            "AVANCE SUR SALAIRE",
            "VIR CC ACF URSSAF RHONE ALPE",
            "VIR STE FINANCIERE DU PORTE",
            "VIR DGFIP FINANCES PUBLIQUES",
            "VIR DRFIP GRAND EST ET DEPT - MENSUALISE \d+ M\d+ REMB. EXCD. IMPOT",
            "VIR DRFIP GRAND EST ET DEPT - REMB. EXCD. IMPOT",
            "VIR KEOLIS LYON",
        ];

        $positivExceptions = [
            ["PAIEMENT CB  0402 PAYLI2441535/ - AMAZON PAYMENTS", '05/02/2021', 3.99],
            ["PAIEMENT CB  0402 PAYLI2441535/ - AMAZON PAYMENTS", '05/02/2021', 15.99],
            ["PAIEMENT CB  1906 PAYLI2441535/ - AMAZON PAYMENTS", '23/06/2020', 16.59],
            ["PAIEMENT CB  2606 PAYLI2441535/ - AMAZON PAYMENTS", '29/06/2020', 16.99],
            ["PAIEMENT CB  0405 PAYLI2441535/ - AMAZON PAYMENTS", '05/05/2020', 10.5],
            ["PAIEMENT CB  1105 PAYLI2441535/ - AMAZON PAYMENTS", '12/05/2020', 12.88],
            ["PAIEMENT CB  0405 ARCHAMPS - BOTANIC", '05/05/2020', 7.55],
            ["PAIEMENT CB  0302 PARIS - LEBONCOIN", '05/02/2021', 11.49],
            ["PAIEMENT CB  1412 PARIS CEDEX 0 - SNCF INTERNET", '15/12/2017', 79.8],
            ["PAIEMENT CB  2810 PARIS - MGP*ULULE SAS", '30/10/2020', 40],
            ["PAIEMENT CB  2905 0800 942 890 - PAYPAL", '01/06/2020', 8.82],
            ["PAIEMENT CB  0812 0800 942 890 - PAYPAL", '09/12/2019', 29.41],
        ];

        // Impossible de diférencier les débits et crédits
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

        foreach ($operations as $operationLine) {
            /** @var \DateTime $date */
            $date = $operationLine[0];
            $name = $operationLine[1];
            $amount = $operationLine[2];
            $isPositiv = false;

            if  (StringHelper::contains($name, $positivLabels) === true) {
                $isPositiv = true;
            }
            foreach ($positivExceptions as $exception) {
                if (strpos($name, $exception[0]) > -1 && $date->format('d/m/Y') === $exception[1] && $amount == $exception[2]) {
                    $isPositiv = true;
                }
            }

            $amount = $isPositiv ? $amount : -$amount;
            $totalAmount += $amount;

            if ($amount > 0) {
                dump($name . ' : ' . $amount);
            }

            if (!$this->operationRepository->findDateNameAmount($date, $name, $amount)) {
                $operation = new Operation();
                $operation->setDate($date);
                $operation->setName($name);
                $operation->setAmount($amount);

                $this->analyseOperation($operation);

                $this->entityManager->persist($operation);
                $nbOperations++;
            }
        }

        if (round($startAmount + $totalAmount, 2) != round($endAmount, 2)) {
            dump($filePath);
            dump("Start", $startAmount, "End : ", $endAmount, "Total : ", $startAmount + $totalAmount);
            foreach ($operations as $operationLine) {
                dump($operationLine);
            }
            throw new \Exception('Les comptes ne tombent pas juste');
        }

        if ($nbOperations === 0) {
            throw new \Exception('Aucune ligne ajouté');
        }

        $this->entityManager->flush();
    }

    public function analyseOperation(Operation $operation)
    {

    }
}
