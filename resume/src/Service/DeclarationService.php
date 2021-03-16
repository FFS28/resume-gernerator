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

    /** @var DeclarationTypeService */
    public $declarationTypeSocial;

    /** @var DeclarationTypeService */
    public $declarationTypeImpot;

    /** @var DeclarationTypeService */
    public $declarationTypeTva;

    /** @var DeclarationTypeService */
    public $declarationTypeCfe;

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

        $this->declarationTypeSocial = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            Declaration::TYPE_SOCIAL
        );

        $this->declarationTypeTva = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            Declaration::TYPE_TVA
        );

        $this->declarationTypeImpot = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            Declaration::TYPE_IMPOT
        );

        $this->declarationTypeCfe = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            Declaration::TYPE_CFE
        );
    }

    /**
     * @return array
     */
    public function getNextDueDate() {
        $dueDates = [];
        $socialDueDates = $this->declarationTypeSocial->getNextDueDate();
        if (count($socialDueDates) > 0) {
            $dueDates[] = $socialDueDates;
        }
        $impotDueDates = $this->declarationTypeImpot->getNextDueDate();
        if (count($impotDueDates) > 0) {
            $dueDates[] = $impotDueDates;
        }
        $tvaDueDates = $this->declarationTypeTva->getNextDueDate();
        if (count($tvaDueDates) > 0) {
            $dueDates[] = $tvaDueDates;
        }
        $cfeDueDates = $this->declarationTypeCfe->getNextDueDate();
        if (count($cfeDueDates) > 0) {
            $dueDates[] = $cfeDueDates;
        }

        /** @var \DateTime[] $dueDate */
        $firstDueDate = null;
        foreach ($dueDates as $dueDate) {
            if (!$firstDueDate || $firstDueDate[2]->diff($dueDate[2])->invert) {
                $firstDueDate = $dueDate;
            }
        }

        return $firstDueDate;
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
                    case Declaration::TYPE_CFE:
                        break;

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

            case Declaration::TYPE_CFE:
                break;
        }

        $this->entityManager->flush();
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

        $nextSocialDueDate = $this->declarationTypeSocial->getNextDueDate($date);
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

            $declarationSocial = $this->declarationTypeSocial->getDeclarations();

            if ($socialDueDateIsActive && $declarationSocial->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration social en cours du T'.$socialDueDate.' à faire entre' .
                    ' le '. $socialDueDateBegin->format('d/m/Y') . ' et le ' . $socialDueDateEnd->format('d/m/Y');
            }
        }

        $nextTvaDueDate = $this->declarationTypeTva->getNextDueDate($date);
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

            $declarationTva = $this->declarationTypeTva->getDeclarations();

            if ($tvaDueDateIsActive && $declarationTva->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration TVA en cours à faire entre' .
                    ' le '. $tvaDueDateBegin->format('d/m/Y') . ' et le ' . $tvaDueDateEnd->format('d/m/Y');
            }
        }

        $nextImpotDueDate = $this->declarationTypeImpot->getNextDueDate($date);
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

            $declarationImpot = $this->declarationTypeImpot->getDeclarations();

            if ($impotDueDateIsActive && $declarationImpot->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration d\'Impots en cours à faire entre' .
                    ' le '. $impotDueDateBegin->format('d/m/Y') . ' et le ' . $impotDueDateEnd->format('d/m/Y');
            }
        }

        $nextCfeDueDate = $this->declarationTypeCfe->getNextDueDate($date);
        if (count($nextCfeDueDate) > 0) {
            /** @var int $cfeDueDate */
            /** @var \DateTime $cfeDueDateBegin */
            /** @var \DateTime $cfeDueDateEnd */
            /** @var bool $cfeDueueDateIsActive */
            list(
                $cfeDueDate,
                $cfeDueDateBegin,
                $cfeDueDateEnd,
                $cfeDueDateIsActive
                ) = $nextCfeDueDate;

            $declarationCfe = $this->declarationTypeCfe->getDeclarations();

            if ($cfeDueDateIsActive && $declarationCfe->getStatus() !== Declaration::STATUS_PAYED) {
                $messages[] = 'Déclaration de CFE en cours à faire entre' .
                    ' le '. $cfeDueDateBegin->format('d/m/Y') . ' et le ' . $cfeDueDateEnd->format('d/m/Y');
            }
        }

        return $messages;
    }
}
