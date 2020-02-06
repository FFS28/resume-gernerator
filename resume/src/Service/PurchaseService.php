<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Purchase;
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
     * @return string
     * @throws Exception
     */
    public function proofToText(Purchase $purchase): string
    {
        if (!$purchase->getProof()) {
            return '';
        }

        $filePath = $this->proofDirectory . $purchase->getProof();

        $fileData = fopen($filePath, 'r');
        $client = new Client();

        try {
            $r = $client->request('POST', 'https://api.ocr.space/parse/image', [
                'headers' => ['apiKey' => '8358716ddb88957'],
                'multipart' => [
                    [
                        'name' => 'file',
                        'contents' => $fileData
                    ]
                ]
            ]);
            $response = json_decode($r->getBody(), true);

            return $response['ParsedResults'][0]['ParsedText'];

        } catch (Exception $err) {
            throw $err;
        }
    }

    /**
     * Analyse le text d'un justificatif pour extraire les montants HT / TVA / TTC
     * @param Purchase $purchase
     * @param string $proof
     */
    public function importProofAmount(Purchase $purchase, string $proof)
    {
        //$this->entityManager->flush();
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