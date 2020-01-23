<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Period;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
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

    /** @var InvoiceRepository */
    private $invoiceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeclarationRepository $declarationRepository,
        PeriodRepository $periodRepository,
        InvoiceRepository $invoiceRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->declarationRepository = $declarationRepository;
        $this->periodRepository = $periodRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * @return int[]
     */
    public function getDueSocialMonth()
    {
        return [
            4,
            7,
            10,
            1
        ];
    }

    /**
     * @return int[]
     */
    public function getDueTvaMonth()
    {
        return [
            7,
            12,
            5
        ];
    }

    public function getAccountingYear(\DateTime $date): int
    {
        $lastMonth = $this->getDueSocialMonth()[3];
        $year = intval($date->format('Y'));
        $month = intval($date->format('m'));

        if ($month == $lastMonth) {
            $year -= 1;
        }

        return $year;
    }

    public function isDueSocialMonth(\DateTime $date)
    {
        $dueDatesMonth = $this->getDueSocialMonth();

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $isDueSocialMonth = intval($date->format('m')) ===  $dueDateMonth;

            if ($isDueSocialMonth) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getDueSocialDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = $this->getDueSocialMonth();
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
        $dueDates = $this->getDueSocialDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate)
        {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate >= $dueDate[1] && $currentDate <= $dueDate[2];
                return $dueDate;
            }
        }

        return [];
    }

    public function calculate(Declaration $declaration)
    {
        if ($declaration->getStatus() === Declaration::STATUS_PAYED) {
            return;
        }

        $revenues = 0;
        $tva = 0;
        foreach ($declaration->getInvoices() as $invoice) {
            switch ($declaration->getType()) {
                case Declaration::TYPE_IMPOT:
                case Declaration::TYPE_SOCIAL:
                    $revenues += $invoice->getTotalHt();
                    break;

                case Declaration::TYPE_TVA:
                    if ($invoice->getTotalTax()) {
                        $revenues += $invoice->getTotalHt();
                        $tva += $invoice->getTotalTax();
                    }
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
                $declaration->setTax($tva);
                break;
        }

        $this->entityManager->flush();
    }

    public function attachInvoice(Invoice $invoice)
    {
        if (!$invoice->getPayedAt()) {
            return ;
        }

        // @TODO Refacto
        /*list ($annualyPeriod, $quarterlyPeriod, $shiftedQuarterlyPeriod)
            = $this->getPeriod($invoice->getPayedAtYear(), $invoice->getPayedAtQuarter());

        $invoice->setPeriod($quarterlyPeriod);

        $this->generateDeclarations($annualyPeriod, $quarterlyPeriod, $shiftedQuarterlyPeriod);*/
    }

    /**
     * @param $year
     * @param $quarter
     * @return Period[]
     *
    public function getPeriod($year, $quarter)
    {
        $annualyPeriod = $this->periodRepository->findOneBy([
            'year' => $year
        ]);
        $quarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $year,
            'quarter' => $quarter
        ]);
        $shiftedQuarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $year - 1,
            'quarter' => $quarter
        ]);

        return [$annualyPeriod, $quarterlyPeriod, $shiftedQuarterlyPeriod];
    }*/

    /**
     * @param Period $annualyPeriod
     * @param Period $quarterlyPeriod
     * @return Declaration[]
     *
    public function generateDeclarations(Period $annualyPeriod, Period $quarterlyPeriod, Period $shiftedQuarterlyPeriod)
    {
        $return = [];

        $declarationImpot = $this->declarationRepository->getByDate(Declaration::TYPE_IMPOT, $annualyPeriod->getYear());

        if (!$declarationImpot) {
            $declarationImpot = new Declaration();
            $declarationImpot->setType(Declaration::TYPE_IMPOT);
            $declarationImpot->setRevenue(0);
            $declarationImpot->setTax(0);
            $declarationImpot->setPeriod($annualyPeriod);

            $this->entityManager->persist($declarationImpot);
        }
        $return[] = $declarationImpot;

        $declarationTva = $this->declarationRepository->getByDate(Declaration::TYPE_TVA, $annualyPeriod->getYear());

        if (!$declarationTva) {
            $declarationTva = new Declaration();
            $declarationTva->setType(Declaration::TYPE_TVA);
            $declarationTva->setRevenue(0);
            $declarationTva->setTax(0);
            $declarationTva->setPeriod($annualyPeriod);

            $this->entityManager->persist($declarationTva);
        }
        $return[] = $declarationTva;

        $declarationSocial = $this->declarationRepository->getByDate(
            Declaration::TYPE_SOCIAL,
            $quarterlyPeriod->getYear(),
            $quarterlyPeriod->getQuarter()
        );

        if (!$declarationSocial) {
            $declarationSocial = new Declaration();
            $declarationSocial->setType(Declaration::TYPE_SOCIAL);
            $declarationSocial->setRevenue(0);
            $declarationSocial->setTax(0);
            $declarationSocial->setPeriod($shiftedQuarterlyPeriod);

            $this->entityManager->persist($declarationSocial);
        }
        $return[] = $declarationSocial;

        $shiftedDeclarationSocial = $this->declarationRepository->getByDate(
            Declaration::TYPE_SOCIAL,
            $shiftedQuarterlyPeriod->getYear(),
            $shiftedQuarterlyPeriod->getQuarter()
        );

        if (!$shiftedDeclarationSocial) {
            $shiftedDeclarationSocial = new Declaration();
            $shiftedDeclarationSocial->setType(Declaration::TYPE_SOCIAL);
            $shiftedDeclarationSocial->setRevenue(0);
            $shiftedDeclarationSocial->setTax(0);
            $shiftedDeclarationSocial->setPeriod($shiftedQuarterlyPeriod);

            $this->entityManager->persist($shiftedDeclarationSocial);
        }
        $return[] = $shiftedDeclarationSocial;

        $this->entityManager->flush();
        
        return $return;
    }*/

    /**
     * Envoi un mail si la date de fin de déclaration approche
     * @return array
     * @throws \Exception
     */
    public function getNotifications()
    {
        $date = new \DateTime();
        $messages = [];


        /** @var int $quarterDueDate */
        /** @var \DateTime $quarterDueDateBegin */
        /** @var \DateTime $quarterDueDateEnd */
        /** @var bool $quarterDueueDateIsActive */
        /*list(
            $quarterDueDate,
            $quarterDueDateBegin,
            $quarterDueDateEnd,
            $quarterDueueDateIsActive
        ) = $this->getNextQuarterDueDate();

        list ($annualyPeriod, $quarterlyPeriod, $shiftedQuarterlyPeriod)
            = $this->getPeriod($quarterDueDateBegin->format('Y'), $quarterDueDate);

        list($declarationImpot, $declarationTva, $declarationSocial, $shiftedDeclarationSocial)
            = $this->generateDeclarations($annualyPeriod, $quarterlyPeriod, $shiftedQuarterlyPeriod);

        if ($quarterDueueDateIsActive && $declarationSocial->getStatus() !== Declaration::STATUS_PAYED) {
            $messages[] = 'Déclaration en cours du T'.$quarterDueDate.' à faire entre' .
                ' le '. $quarterDueDateBegin->format('d/m/Y') . ' et le ' . $quarterDueDateEnd->format('d/m/Y');
        }*/

        return $messages;
    }
}