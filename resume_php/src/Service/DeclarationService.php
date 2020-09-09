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

    /** @var PeriodService */
    private $periodService;

    /** @var InvoiceRepository */
    private $invoiceRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        DeclarationRepository $declarationRepository,
        PeriodRepository $periodRepository,
        PeriodService $periodService,
        InvoiceRepository $invoiceRepository
    )
    {
        $this->entityManager = $entityManager;
        $this->declarationRepository = $declarationRepository;
        $this->periodRepository = $periodRepository;
        $this->periodService = $periodService;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Retourne les mois de declaration sociales
     * @return int[]
     */
    public static function getDueSocialMonth()
    {
        return [
            4,
            7,
            10,
            1
        ];
    }

    /**
     * Retourne les mois de déclaration de TVA
     * @return int[]
     */
    public static function getDueTvaMonth()
    {
        return [
            7,
            12,
            5
        ];
    }

    /**
     * Retourne les mois de déclaration des Impots
     * @return int[]
     */
    public static function getDueImpotMonth()
    {
        return [
            5
        ];
    }

    /**
     * Retourne l'année comptable
     * @param \DateTime $date
     * @return int
     */
    public function getAccountingYear(\DateTime $date): int
    {
        $lastMonth = DeclarationService::getDueSocialMonth()[3];
        $year = intval($date->format('Y'));
        $month = intval($date->format('m'));

        if ($month == $lastMonth) {
            $year -= 1;
        }

        return $year;
    }

    /**
     * Verifie si on est dans un mois de déclaration sociale
     * @return bool
     * @throws \Exception
     */
    public function isDueSocialMonth()
    {
        $date = new \DateTime();
        $dueDatesMonth = DeclarationService::getDueSocialMonth();

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $isDueSocialMonth = intval($date->format('m')) ===  $dueDateMonth;

            if ($isDueSocialMonth) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifie si on est dans un mois de déclaration sociale
     * @return bool
     * @throws \Exception
     */
    public function isDueTvaMonth()
    {
        $date = new \DateTime();
        $dueDatesMonth = DeclarationService::getDueTvaMonth();

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $isDueSocialMonth = intval($date->format('m')) ===  $dueDateMonth;

            if ($isDueSocialMonth) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifie si on est dans un mois de déclaration sociale
     * @return bool
     * @throws \Exception
     */
    public function isDueImpotMonth()
    {
        $date = new \DateTime();
        $dueDatesMonth = DeclarationService::getDueImpotMonth();

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $isDueSocialMonth = intval($date->format('m')) ===  $dueDateMonth;

            if ($isDueSocialMonth) {
                return true;
            }
        }

        return false;
    }

    /**
     * Récupère les dates courantes de déclarations sociales
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getDueSocialDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = DeclarationService::getDueSocialMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new \DateTime($this->getAccountingYear($date).'-'.($dueDateMonth < 10 ? '0' : '').$dueDateMonth.'-01');
            if ($index == 3) {
                $dueDateBegin->add(new \DateInterval('P1Y'));
            }

            $dueDates[] = [
                $index + 1,
                clone $dueDateBegin,
                $dueDateBegin
                    ->add(new \DateInterval('P'.(intval($dueDateBegin->format('t')) - 1).'D'))
                    ->add(new \DateInterval('PT23H59M59S'))
            ];
        }

        return $dueDates;
    }

    /**
     * Récupère les dates courantes de déclarations sociales
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getDueTvaDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = DeclarationService::getDueTvaMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new \DateTime($this->getAccountingYear($date).'-'.($dueDateMonth < 10 ? '0' : '').$dueDateMonth.'-01');
            if ($index == 3) {
                $dueDateBegin->add(new \DateInterval('P1Y'));
            }

            $dueDates[] = [
                $index + 1,
                clone $dueDateBegin,
                $dueDateBegin
                    ->add(new \DateInterval('P'.(intval($dueDateBegin->format('t')) - 1).'D'))
                    ->add(new \DateInterval('PT23H59M59S'))
            ];
        }

        return $dueDates;
    }

    /**
     * Récupère les dates courantes de déclarations sociales
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public function getDueImpotDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = DeclarationService::getDueImpotMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new \DateTime($this->getAccountingYear($date).'-'.($dueDateMonth < 10 ? '0' : '').$dueDateMonth.'-01');
            if ($index == 3) {
                $dueDateBegin->add(new \DateInterval('P1Y'));
            }

            $dueDates[] = [
                $index + 1,
                clone $dueDateBegin,
                $dueDateBegin
                    ->add(new \DateInterval('P'.(intval($dueDateBegin->format('t')) - 1).'D'))
                    ->add(new \DateInterval('PT23H59M59S'))
            ];
        }

        return $dueDates;
    }

    /**
     * Retourne les dates de début et fin du prochain trimestre de cotisation social
     * @param \DateTime|null $date
     * @return array
     * @throws \Exception
     */
    public function getNextSocialDueDate(\DateTime $date = null): array
    {
        $currentDate = $date ? $date : new \DateTime();
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

    /**
     * Retourne les dates de début et fin du prochain trimestre
     * @param \DateTime|null $date
     * @return array
     * @throws \Exception
     */
    public function getNextTvaDueDate(\DateTime $date = null): array
    {
        $currentDate = $date ? $date : new \DateTime();
        $dueDates = $this->getDueTvaDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate)
        {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate >= $dueDate[1] && $currentDate <= $dueDate[2];
                return $dueDate;
            }
        }

        return [];
    }

    /**
     * Retourne les dates de début et fin du prochain trimestre
     * @param \DateTime|null $date
     * @return array
     * @throws \Exception
     */
    public function getNextImpotDueDate(\DateTime $date = null): array
    {
        $currentDate = $date ? $date : new \DateTime();
        $dueDates = $this->getDueImpotDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate)
        {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate >= $dueDate[1] && $currentDate <= $dueDate[2];
                return $dueDate;
            }
        }

        return [];
    }

    /**
     * Calcule le montant d'une déclaration
     * @param Declaration $declaration
     */
    public function calculate(Declaration $declaration)
    {
        if ($declaration->getStatus() === Declaration::STATUS_PAYED) {
            return;
        }

        $revenues = 0;
        $tva = 0;
        foreach ($declaration->getInvoices() as $invoice) {
            if ($invoice->getStatus() === Invoice::STATUS_PAYED) {
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
        }
        $declaration->setRevenue($revenues);

        if ($declaration->getType() === Declaration::TYPE_TVA) {
            foreach ($declaration->getPurchases() as $purchase) {
                $tva -= $purchase->getTotalTax();
            }
        }

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
     * Récupère la déclaration sociale courante
     * @param bool $forceCurrent
     * @return Declaration|mixed
     * @throws \Exception
     */
    public function getSocialDeclarations($forceCurrent = false)
    {
        list ($annualyPeriod, $quarterlyPeriod)
            = $this->isDueSocialMonth() && !$forceCurrent ? $this->periodService->getPreviousPeriod() : $this->periodService->getCurrentPeriod();

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

    /**
     * Récupère la déclarations TVA courante
     * @param bool $forceCurrent
     * @return Declaration|mixed
     * @throws \Exception
     */
    public function getTvaDeclarations($forceCurrent = false)
    {
        list ($annualyPeriod) = $this->periodService->getCurrentPeriod();

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

    /**
     * Récupère la déclaration d'impot courante
     * @param bool $forceCurrent
     * @return Declaration|mixed
     * @throws \Exception
     */
    public function getImpotDeclarations($forceCurrent = false)
    {
        list ($annualyPeriod) = $this->periodService->getCurrentPeriod();

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

        $nextSocialDueDate = $this->getNextSocialDueDate($date);
        if (count($nextSocialDueDate) > 0) {
            /** @var int $socialDueDate */
            /** @var \DateTime $socialDueDateBegin */
            /** @var \DateTime $socialDueDateEnd */
            /** @var bool $socialDueueDateIsActive */
            list(
                $socialDueDate,
                $socialDueDateBegin,
                $socialDueDateEnd,
                $socialDueDateIsActive
                ) = $nextSocialDueDate;

            $declarationSocial = $this->getSocialDeclarations();

            if ($socialDueDateIsActive && $declarationSocial->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration social en cours du T'.$socialDueDate.' à faire entre' .
                    ' le '. $socialDueDateBegin->format('d/m/Y') . ' et le ' . $socialDueDateEnd->format('d/m/Y');
            }
        }

        $nextTvaDueDate = $this->getNextTvaDueDate($date);
        if (count($nextTvaDueDate) > 0) {
            /** @var int $tvaDueDate */
            /** @var \DateTime $tvaDueDateBegin */
            /** @var \DateTime $tvaDueDateEnd */
            /** @var bool $tvaDueueDateIsActive */
            list(
                $tvaDueDate,
                $tvaDueDateBegin,
                $tvaDueDateEnd,
                $tvaDueDateIsActive
                ) = $nextTvaDueDate;

            $declarationTva = $this->getTvaDeclarations();

            if ($tvaDueDateIsActive && $declarationTva->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration TVA en cours à faire entre' .
                    ' le '. $tvaDueDateBegin->format('d/m/Y') . ' et le ' . $tvaDueDateEnd->format('d/m/Y');
            }
        }

        $nextImpotDueDate = $this->getNextImpotDueDate($date);
        if (count($nextImpotDueDate) > 0) {
            /** @var int $impotDueDate */
            /** @var \DateTime $impotDueDateBegin */
            /** @var \DateTime $impotDueDateEnd */
            /** @var bool $impotDueueDateIsActive */
            list(
                $impotDueDate,
                $impotDueDateBegin,
                $impotDueDateEnd,
                $impotDueDateIsActive
                ) = $nextImpotDueDate;

            $declarationImpot = $this->getImpotDeclarations();

            if ($impotDueDateIsActive && $declarationImpot->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration d\'Impots en cours à faire entre' .
                    ' le '. $impotDueDateBegin->format('d/m/Y') . ' et le ' . $impotDueDateEnd->format('d/m/Y');
            }
        }

        return $messages;
    }
}
