<?php

namespace App\Entity;

use App\Enum\DeclarationStatusEnum;
use App\Enum\DeclarationTypeEnum;
use App\Repository\DeclarationRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: DeclarationRepository::class)]
class Declaration implements Stringable
{
    final const SOCIAL_NON_COMMERCIALE = 0.22;
    final const SOCIAL_CFP = 0.002;
    final const IMPOT_ABATTEMENT = 0.34;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $revenue = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $tax = null;

    #[ORM\Column(type: Types::STRING, enumType: DeclarationTypeEnum::class)]
    private ?DeclarationTypeEnum $type = null;

    #[ORM\ManyToOne(targetEntity: Period::class, inversedBy: 'declarations')]
    private ?Period $period = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: DeclarationStatusEnum::class)]
    private ?DeclarationStatusEnum $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $payedAt = null;

    public function __construct()
    {
        $this->setStatus(DeclarationStatusEnum::Waiting);
    }

    public function __toString(): string
    {
        $str = ucfirst($this->getType()->toString()) . ' ';
        $period = $this->getPeriod();

        if ($period->getYear()) {
            $str .= $period->getYear();
        }
        if ($period->getQuarter()) {
            $str .= ' T' . $period->getQuarter();
        }

        return $str;
    }

    public function getType(): ?DeclarationTypeEnum
    {
        return $this->type;
    }

    public function setType(DeclarationTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getPeriod(): ?Period
    {
        return $this->period;
    }

    public function setPeriod(?Period $period): self
    {
        $this->period = $period;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getRate(): float
    {
        return $this->getRevenue() > 0
            ? round($this->getTax() * 100 / $this->getRevenue(), 2)
            : 0;
    }

    public function getRevenue(): ?string
    {
        return $this->revenue;
    }

    public function setRevenue(string $revenue): self
    {
        $this->revenue = $revenue;

        return $this;
    }

    public function getTax(): ?string
    {
        return $this->tax;
    }

    public function setTax(?string $tax): self
    {
        $this->tax = $tax;

        return $this;
    }

    public function getTypeName(): ?string
    {
        return $this->type->toString();
    }

    public function getStatus(): ?DeclarationStatusEnum
    {
        return $this->status;
    }

    public function setStatus(DeclarationStatusEnum $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getStatusName(): ?string
    {
        return $this->status->toString();
    }

    public function getPayedAt(): ?DateTimeInterface
    {
        return $this->payedAt;
    }

    public function setPayedAt(?DateTimeInterface $payedAt): self
    {
        $this->payedAt = $payedAt;

        return $this;
    }

    /**
     * @return Invoice[]
     */
    public function getInvoices(): array
    {
        $period = $this->getPeriod();
        if (!$period) {
            return [];
        }

        $periodsQuarter = $period->getPeriodsQuarter();

        if (count($periodsQuarter)) {
            $invoices = [];
            foreach ($periodsQuarter as $period) {
                $invoices = array_merge($invoices, $period->getPayedInvoices());
            }
            return $invoices;
        }

        return $period->getPayedInvoices();
    }
}
