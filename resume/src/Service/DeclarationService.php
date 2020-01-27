<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Period;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;
use function Sodium\add;

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

    public function isDueSocialMonth()
    {
        $date = new \DateTime();
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

    /**
     * @param \DateTime $date
     * @return Period[]
     * @throws \Exception
     */
    public function getCurrentPeriod($date = null)
    {
        if (!$date) {
            $date = new \DateTime();
        }

        $year = intval($date->format('Y'));

        $annualyPeriod = $this->periodRepository->findOneBy([
            'year' => $year
        ]);
        $quarterlyPeriod = $this->periodRepository->findOneBy([
            'year' => $year,
            'quarter' => ceil(intval($date->format('n')) / 3)
        ]);

        return [$annualyPeriod, $quarterlyPeriod];
    }


    /**
     * @return Period[]
     * @throws \Exception
     */
    public function getPreviousPeriod()
    {
        $date = (new \DateTime())->sub(new \DateInterval('P1M'));
        return $this->getCurrentPeriod($date);
    }

    public function getSocialDeclarations($forceCurrent = false)
    {
        list ($annualyPeriod, $quarterlyPeriod)
            = $this->isDueSocialMonth() && !$forceCurrent ? $this->getPreviousPeriod() : $this->getCurrentPeriod();

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
            $declarationSocial->setPeriod($quarterlyPeriod);

            $this->entityManager->persist($declarationSocial);
        }

        $this->entityManager->flush();

        return $declarationSocial;
    }

    public function getTvaDeclarations($forceCurrent = false)
    {
        list ($annualyPeriod) = $this->getCurrentPeriod();

        $declarationTva = $this->declarationRepository->getByDate(Declaration::TYPE_TVA, $annualyPeriod->getYear());

        if (!$declarationTva) {
            $declarationTva = new Declaration();
            $declarationTva->setType(Declaration::TYPE_TVA);
            $declarationTva->setRevenue(0);
            $declarationTva->setTax(0);
            $declarationTva->setPeriod($annualyPeriod);

            $this->entityManager->persist($declarationTva);
        }

        $this->entityManager->flush();

        return $declarationTva;
    }

    public function getImpotDeclarations($forceCurrent = false)
    {
        list ($annualyPeriod) = $this->getCurrentPeriod();

        $declarationImpot = $this->declarationRepository->getByDate(Declaration::TYPE_IMPOT, $annualyPeriod->getYear());

        if (!$declarationImpot) {
            $declarationImpot = new Declaration();
            $declarationImpot->setType(Declaration::TYPE_IMPOT);
            $declarationImpot->setRevenue(0);
            $declarationImpot->setTax(0);
            $declarationImpot->setPeriod($annualyPeriod);

            $this->entityManager->persist($declarationImpot);
        }

        $this->entityManager->flush();

        return $declarationImpot;
    }

    /**
     * Envoi un mail si la date de fin de déclaration approche
     * @return string[]
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
        list(
            $quarterDueDate,
            $quarterDueDateBegin,
            $quarterDueDateEnd,
            $quarterDueueDateIsActive
        ) = $this->getNextQuarterDueDate();

        $declarationSocial = $this->getSocialDeclarations();
        $declarationImpot = $this->getImpotDeclarations();
        $declarationTva = $this->getTvaDeclarations();

        if ($quarterDueueDateIsActive && $declarationSocial->getStatus() !== Declaration::STATUS_PAYED) {
            $messages[] = 'Déclaration en cours du T'.$quarterDueDate.' à faire entre' .
                ' le '. $quarterDueDateBegin->format('d/m/Y') . ' et le ' . $quarterDueDateEnd->format('d/m/Y');
        }

        return $messages;
    }
}