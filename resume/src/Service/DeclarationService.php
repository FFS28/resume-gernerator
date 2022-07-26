<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Enum\DeclarationStatusEnum;
use App\Enum\DeclarationTypeEnum;
use App\Enum\InvoiceStatusEnum;
use App\Repository\DeclarationRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class DeclarationService
{
    public DeclarationTypeService $declarationTypeSocial;

    public DeclarationTypeService $declarationTypeImpot;

    public DeclarationTypeService $declarationTypeTva;

    public DeclarationTypeService $declarationTypeCfe;

    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        DeclarationRepository                   $declarationRepository,
        PeriodService                           $periodService,
    ) {
        $this->declarationTypeSocial = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            DeclarationTypeEnum::Social
        );

        $this->declarationTypeTva = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            DeclarationTypeEnum::TVA
        );

        $this->declarationTypeImpot = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            DeclarationTypeEnum::Impot
        );

        $this->declarationTypeCfe = new DeclarationTypeService(
            $entityManager,
            $declarationRepository,
            $periodService,
            DeclarationTypeEnum::CFE
        );
    }

    /**
     * Calcule le montant d'une déclaration
     */
    public function calculate(Declaration $declaration): void
    {
        if ($declaration->getStatus() === DeclarationStatusEnum::Payed) {
            return;
        }

        $revenues = 0;
        $tva = 0;
        foreach ($declaration->getInvoices() as $invoice) {
            if ($invoice->getStatus() === InvoiceStatusEnum::Payed) {
                switch ($declaration->getType()) {
                    case DeclarationTypeEnum::CFE:
                        break;

                    case DeclarationTypeEnum::Impot:
                    case DeclarationTypeEnum::Social:
                        $revenues += $invoice->getTotalHt();
                        break;

                    case DeclarationTypeEnum::TVA:
                        if ($invoice->getTotalTax()) {
                            $revenues += $invoice->getTotalHt();
                            $tva += $invoice->getTotalTax();
                        }
                        break;

                }
            }
        }
        $declaration->setRevenue($revenues);

        switch ($declaration->getType()) {
            case DeclarationTypeEnum::Social:
                $declaration->setTax(
                    $revenues *
                    (Declaration::SOCIAL_NON_COMMERCIALE + Declaration::SOCIAL_CFP)
                );
                break;

            case DeclarationTypeEnum::TVA:
                $declaration->setTax($tva);
                break;

            case DeclarationTypeEnum::Impot:
            case DeclarationTypeEnum::CFE:
                break;
        }

        $this->entityManager->flush();
    }

    /**
     * Envoi un mail si la date de fin de déclaration approche
     * @return string[]
     * @throws Exception
     */
    public function getNotifications(): array
    {
        $date = new DateTime();
        $messages = [];

        $nextSocialDueDate = $this->declarationTypeSocial->getNextDueDate($date);
        if (count($nextSocialDueDate) > 0) {
            /** @var int $socialDueDate */
            /** @var DateTime $socialDueDateBegin */
            /** @var DateTime $socialDueDateEnd */
            /** @var bool $socialDueueDateIsActive */
            [$socialDueDate, $socialDueDateBegin, $socialDueDateEnd, $socialDueDateIsActive] = $nextSocialDueDate;

            $declarationSocial = $this->declarationTypeSocial->getDeclarations();

            if ($socialDueDateIsActive && $declarationSocial->getStatus() !== DeclarationStatusEnum::Payed) {
                $messages[] = 'Déclaration social en cours du T' . $socialDueDate . ' à faire entre' .
                    ' le ' . $socialDueDateBegin->format('d/m/Y') . ' et le ' . $socialDueDateEnd->format('d/m/Y');
            }
        }

        $nextTvaDueDate = $this->declarationTypeTva->getNextDueDate($date);
        if (count($nextTvaDueDate) > 0) {
            /** @var int $tvaDueDate */
            /** @var DateTime $tvaDueDateBegin */
            /** @var DateTime $tvaDueDateEnd */
            /** @var bool $tvaDueueDateIsActive */
            [$tvaDueDate, $tvaDueDateBegin, $tvaDueDateEnd, $tvaDueDateIsActive] = $nextTvaDueDate;

            $declarationTva = $this->declarationTypeTva->getDeclarations();

            if ($tvaDueDateIsActive && $declarationTva->getStatus() !== DeclarationStatusEnum::Payed) {
                $messages[] = 'Déclaration TVA en cours à faire entre' .
                    ' le ' . $tvaDueDateBegin->format('d/m/Y') . ' et le ' . $tvaDueDateEnd->format('d/m/Y');
            }
        }

        $nextImpotDueDate = $this->declarationTypeImpot->getNextDueDate($date);
        if (count($nextImpotDueDate) > 0) {
            /** @var int $impotDueDate */
            /** @var DateTime $impotDueDateBegin */
            /** @var DateTime $impotDueDateEnd */
            /** @var bool $impotDueueDateIsActive */
            [$impotDueDate, $impotDueDateBegin, $impotDueDateEnd, $impotDueDateIsActive] = $nextImpotDueDate;

            $declarationImpot = $this->declarationTypeImpot->getDeclarations();

            if ($impotDueDateIsActive && $declarationImpot->getStatus() !== DeclarationStatusEnum::Payed) {
                $messages[] = 'Déclaration d\'Impots en cours à faire entre' .
                    ' le ' . $impotDueDateBegin->format('d/m/Y') . ' et le ' . $impotDueDateEnd->format('d/m/Y');
            }
        }

        $nextCfeDueDate = $this->declarationTypeCfe->getNextDueDate($date);
        if (count($nextCfeDueDate) > 0) {
            /** @var int $cfeDueDate */
            /** @var DateTime $cfeDueDateBegin */
            /** @var DateTime $cfeDueDateEnd */
            /** @var bool $cfeDueueDateIsActive */
            [$cfeDueDate, $cfeDueDateBegin, $cfeDueDateEnd, $cfeDueDateIsActive] = $nextCfeDueDate;

            $declarationCfe = $this->declarationTypeCfe->getDeclarations();

            if ($cfeDueDateIsActive && $declarationCfe->getStatus() !== DeclarationStatusEnum::Payed) {
                $messages[] = 'Déclaration de CFE en cours à faire entre' .
                    ' le ' . $cfeDueDateBegin->format('d/m/Y') . ' et le ' . $cfeDueDateEnd->format('d/m/Y');
            }
        }

        return $messages;
    }

    /**
     * @throws Exception
     */
    public function getNextDueDate(): array
    {
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

        /** @var DateTime[] $dueDate */
        $firstDueDate = null;
        foreach ($dueDates as $dueDate) {
            if (!$firstDueDate || $firstDueDate[2]->diff($dueDate[2])->invert) {
                $firstDueDate = $dueDate;
            }
        }

        return $firstDueDate;
    }
}
