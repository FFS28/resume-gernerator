<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Repository\DeclarationRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;

class DeclarationService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var DeclarationRepository */
    private $declarationRepository;

    /** @var PeriodRepository */
    private $periodRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeclarationRepository $declarationRepository,
        PeriodRepository $periodRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->declarationRepository = $declarationRepository;
        $this->periodRepository = $periodRepository;
    }

    /**
     * @return int[]
     */
    public function getDueQuarterMonth()
    {
        return [
            4,
            7,
            10,
            1
        ];
    }

    public function getAccountingYear(\DateTime $date): int
    {
        $lastMonth = $this->getDueQuarterMonth()[3];
        $year = intval($date->format('Y'));
        $month = intval($date->format('m'));

        if ($month == $lastMonth) {
            $year -= 1;
        }

        return $year;
    }

    /**
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getDueQuarterDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = $this->getDueQuarterMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new \DateTime($this->getAccountingYear($date).'-'.($dueDateMonth < 10 ? '0' : '').$dueDateMonth.'-01');
            if ($index == 3) {
                $dueDateBegin->add(new \DateInterval('P1Y'));
            }

            $dueDates[] = [
                $index + 1,
                clone $dueDateBegin,
                $dueDateBegin->add(new \DateInterval('P'.(intval($dueDateBegin->format('t')) - 1).'D'))
            ];
        }

        return $dueDates;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function getNextQuarterDueDate(): array
    {
        $currentDate = new \DateTime();
        $dueDates = $this->getDueQuarterDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate)
        {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate >= $dueDate[1] && $currentDate <= $dueDate[2];
                return $dueDate;
            }
        }
    }

    public function attachInvoice(Invoice $invoice)
    {
        if (!$invoice->getPayedAt()) {
            return ;
        }

        $annualyPeriod = $this->periodRepository->findOneBy([
            'year' => $invoice->getPayedAtYear()
        ]);
        $quarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $invoice->getPayedAtYear(),
            'quarter' => $invoice->getPayedAtQuarter()
        ]);

        $invoice->setPeriod($quarterlyPeriod);

        /** @var Declaration $declaration */
        $declaration = $this->declarationRepository->getByInvoice($invoice, Declaration::TYPE_SOCIAL);

        if (!$declaration) {
            $declaration = new Declaration();
            $declaration->setType(Declaration::TYPE_SOCIAL);
            $declaration->setYear($invoice->getPayedAtYear());
            $declaration->setQuarter($invoice->getPayedAtQuarter());
            $declaration->setRevenue(0);
            $declaration->setTax(0);
            $declaration->setPeriod($quarterlyPeriod);

            $this->entityManager->persist($declaration);
        }

        /** @var Declaration $declaration */
        $declaration = $this->declarationRepository->getByInvoice($invoice, Declaration::TYPE_TVA);

        if (!$declaration) {
            $declaration = new Declaration();
            $declaration->setType(Declaration::TYPE_TVA);
            $declaration->setYear($invoice->getPayedAtYear());
            $declaration->setRevenue(0);
            $declaration->setTax(0);
            $declaration->setPeriod($annualyPeriod);

            $this->entityManager->persist($declaration);
        }

        /** @var Declaration $declaration */
        $declaration = $this->declarationRepository->getByInvoice($invoice, Declaration::TYPE_IMPOT);

        if (!$declaration) {
            $declaration = new Declaration();
            $declaration->setType(Declaration::TYPE_IMPOT);
            $declaration->setYear($invoice->getPayedAtYear());
            $declaration->setRevenue(0);
            $declaration->setTax(0);
            $declaration->setPeriod($annualyPeriod);

            $this->entityManager->persist($declaration);
        }

        $this->entityManager->flush();
    }

    public function calculate(Declaration $declaration)
    {
        if ($declaration->getStatus() === Declaration::STATUS_PAYED) {
            return;
        }

        $revenues = 0;
        foreach ($declaration->getInvoices() as $invoice) {
            switch ($declaration->getType()) {
                case Declaration::TYPE_IMPOT:
                case Declaration::TYPE_SOCIAL:
                    $revenues += $invoice->getTotalHt();
                    break;

                case Declaration::TYPE_TVA:
                    $revenues += $invoice->getTotalTax();
                    break;

            }
        }
        $declaration->setRevenue($revenues);

        switch ($declaration->getType()) {
            case Declaration::TYPE_SOCIAL:
                $declaration->setTax($revenues *
                    (Declaration::SOCIAL_NON_COMMERCIALE + Declaration::SOCIAL_CFP)
                );
                break;

            case Declaration::TYPE_TVA:
                $declaration->setTax($revenues);
                break;
        }

        $this->entityManager->flush();
    }
}