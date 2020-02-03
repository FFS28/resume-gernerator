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

class PurchaseService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var PurchaseRepository */
    private $purchaseRepository;

    /** @var PeriodService */
    private $periodService;

    public function __construct(
        EntityManagerInterface $entityManager,
        PurchaseRepository $purchaseRepository,
        PeriodService $periodService
    ) {
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
     * @return array
     * @throws \Exception
     */
    public function getNotifications()
    {
        $messages = [];

        return $messages;
    }
}