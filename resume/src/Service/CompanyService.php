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
     * Envoi un mail si la date de fin de déclaration approche
     * @return array
     * @throws \Exception
     */
    public function getNotifications()
    {
        $messages = [];

        return $messages;
    }
}