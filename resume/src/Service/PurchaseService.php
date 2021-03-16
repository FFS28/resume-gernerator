<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Purchase;
use App\Helper\ImageHelper;
use App\Helper\StringHelper;
use App\Repository\CompanyRepository;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use App\Repository\PurchaseRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Gumlet\ImageResize;
use GuzzleHttp\Client;
//use Imagick;

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
     * @return Purchase
     * @throws Exception
     */
    public function proofToText(Purchase $purchase): Purchase
    {
        if (!$purchase->getProof()) {
            return $purchase;
        }

        $filePath = $this->proofDirectory . $purchase->getProof();
        $maxSize = 1024 * 1024;
        ImageHelper::resizeToSize($filePath, $maxSize);

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
                    [ 'name' => 'OCREngine', 'contents' => '2' ],
                    [
                        'name' => 'file',
                        'contents' => $fileData
                    ]
                ]
            ]);
            $response = json_decode($r->getBody(), true);

            if (isset($response['ParsedResults'])) {
                $purchase->setProofData(explode("\n", $response['ParsedResults'][0]['ParsedText']));
            }
        } catch (Exception $err) {
            throw $err;
        }

        return $purchase;
    }

    /**
     * Analyse le text d'un justificatif pour extraire les montants HT / TVA / TTC
     * @param Purchase $purchase
     */
    public function importProofAmount(Purchase $purchase)
    {
        $tva = 0;
        $regexesTVA = [
            '#TVA\s*\(([\d\.,]+)[\w\%]?\)\s*([\d\.,]+\s*)*(EUR)?#i',
            '#\W?\s*[\s\w\.,]+\s*HT\s*[\@\w]?([\d\.,]+)[\w\%]?\s*([\s\w\.,]+)\s*TVA#i'
        ];
        $ht = 0;
        $regexesHT = [
            '#TOTAL\s*HT[\s:]*([\s\w\.,]+)\s*(EUR)?#i'
        ];
        $ttc = 0;
        $regexesTTC = [
            '#TOTAL\s*TTC[\s:]*([\s\w\.,]+)\s*(EUR)?#i'
        ];

        foreach ($purchase->getProofData() as $line) {
            $line = str_replace(',', '.', $line);

            foreach ($regexesTVA as $index => $regex) {
                preg_match($regex, $line, $matches);

                if (count($matches) > 0) {
                    switch($index) {
                        case 0:
                        case 1:
                            $tvaPercent = floatval(StringHelper::removeSpaces($matches[1]));
                            $tvaValue = floatval(StringHelper::removeSpaces($matches[2]));

                            $tva += $tvaValue;
                            break;
                    }
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

        /*dump($purchase->getProofData());
        dump($ht);
        dump($tva);
        dump($ttc);
        exit;*/

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