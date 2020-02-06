<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Purchase;
use App\Helper\StringHelper;
use App\Repository\CompanyRepository;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use GuzzleHttp\Client;

class PurchaseService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PurchaseRepository */
    private $purchaseRepository;

    /** @var PeriodService */
    private $periodService;

    private $proofDirectory;

    public function __construct(
        string $proofDirectory,
        EntityManagerInterface $entityManager,
        PurchaseRepository $purchaseRepository,
        PeriodService $periodService
    ) {
        $this->proofDirectory = $proofDirectory;
        $this->entityManager = $entityManager;
        $this->purchaseRepository = $purchaseRepository;
        $this->periodService = $periodService;
    }

    /**
     * Met à jour la période d'un achat
     * @param Purchase $invoice
     * @throws \Exception
     */
    public function updatePeriod(Purchase $purchase)
    {
        if (!$purchase->getPayedAt() || $purchase->getPeriod()) {
            return ;
        }

        list ($annualyPeriod, $quarterlyPeriod) = $this->periodService->getCurrentPeriod($purchase->getPayedAt());
        $purchase->setPeriod($annualyPeriod);
    }

    /**
     * Appel à l'API OCR pour transformer le justificatif en texte
     * @param Purchase $purchase
     * @return array
     * @throws Exception
     */
    public function proofToText(Purchase $purchase): array
    {
        if (!$purchase->getProof()) {
            return [];
        }

        if ($purchase->getProofData()) {
            return $purchase->getProofData();
        }

        $filePath = $this->proofDirectory . $purchase->getProof();

        $fileData = fopen($filePath, 'r');
        $client = new Client();

        try {
            $r = $client->request('POST', 'https://api.ocr.space/parse/image', [
                'headers' => [
                    'apiKey' => '8358716ddb88957',
                ],
                'multipart' => [
                    [ 'name' => 'isOverlayRequired', 'contents' => 'true' ],
                    [ 'name' => 'isTable', 'contents' => 'true' ],
                    [ 'name' => 'language', 'contents' => 'fre' ],
                    [ 'name' => 'scale', 'contents' => 'true' ],
                    [ 'name' => 'OCREngine', 'contents' => '1' ],
                    [
                        'name' => 'file',
                        'contents' => $fileData
                    ]
                ]
            ]);
            $response = json_decode($r->getBody(), true);

            if (isset($response['ParsedResults'])) {
                return $response['ParsedResults'];
            }

            return $response;

        } catch (Exception $err) {
            throw $err;
        }
    }

    /**
     * Analyse le text d'un justificatif pour extraire les montants HT / TVA / TTC
     * @param Purchase $purchase
     * @param array $proof
     */
    public function importProofAmount(Purchase $purchase, array $proofData)
    {

        if (!$purchase->getProofData()) {
            $purchase->setProofData(explode("\n", $proofData[0]['ParsedText']));
        }

        $tva = 0;
        $regexesTVA = [
            '#TVA\s*\(([\d\.,]+)[\w\%]?\)\s*([\d\.,]+\s*)*EUR#'
        ];
        $ht = 0;
        $regexesHT = [
            '#TOTAL\s*HT\s*([\s\w\.,]+)\s*EUR#'
        ];
        $ttc = 0;
        $regexesTTC = [
            '#TOTAL\s*TTC\s*([\s\w\.,]+)\s*EUR#'
        ];

        foreach ($purchase->getProofData() as $line) {
            $line = str_replace(',', '.', $line);

            foreach ($regexesTVA as $regex) {
                preg_match($regex, $line, $matches);

                if (count($matches) > 0) {
                    $tvaPercent = floatval(StringHelper::removeSpaces($matches[1]));
                    $tvaValue = floatval(StringHelper::removeSpaces($matches[2]));

                    $tva += $tvaValue;
                    break;
                }
            }
            foreach ($regexesHT as $regex) {
                preg_match($regex, $line, $matches);

                if (count($matches) > 0) {
                    $ht = floatval(StringHelper::removeSpaces($matches[1]));
                    break;
                }
            }
            foreach ($regexesTTC as $regex) {
                preg_match($regex, $line, $matches);

                if (count($matches) > 0) {
                    $ttc = floatval(StringHelper::removeSpaces($matches[1]));
                    break;
                }
            }
        }

        if (!$ht && $tva && $ttc) {
            $ht = $ttc - $tva;
        } elseif (!$tva && $ht && $ttc) {
            $tva = $ttc - $ht;
        } elseif (!$ttc && $ht && $tva) {
            $ttc = $ht + $tva;
        }

        $purchase->setTotalHt($ht);
        $purchase->setTotalTax($tva);
        $this->entityManager->flush();
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getNotifications()
    {
        $messages = [];

        return $messages;
    }
}