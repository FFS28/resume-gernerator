<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Repository\CompanyRepository;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;

class CompanyService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var CompanyRepository */
    private $companyRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeclarationRepository $companyRepository
    ) {
        $this->entityManager = $entityManager;
        $this->companyRepository = $companyRepository;
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