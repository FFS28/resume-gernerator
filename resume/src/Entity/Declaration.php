<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\DeclarationRepository")
 */
class Declaration
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $revenue;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $tax;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    const TYPE_TVA    = "tva";
    const TYPE_SOCIAL = "social";
    const TYPE_IMPOT = "impot";

    /** @var array user friendly named type */
    const TYPES = [
        'TVA' => self::TYPE_TVA,
        'Social' => self::TYPE_SOCIAL,
        'Impot' => self::TYPE_IMPOT,
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Period", inversedBy="declarations")
     */
    private $period;

    const SOCIAL_NON_COMMERCIALE = 0.22;
    const SOCIAL_CFP = 0.002;

    const IMPOT_ABATTEMENT = 0.34;

    const STATUS_WAITING = 'waiting';
    const STATUS_PAYED = 'payed';

    /**
     * @ORM\Column(type="string", length=255, nullable=true))
     */
    private $status;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $payedAt;

    /** @var array user friendly named type */
    const STATUSES = [
        'Waiting' => self::STATUS_WAITING,
        'Payed' => self::STATUS_PAYED,
    ];

    public function __construct()
    {
        $this->setStatus(self::STATUS_WAITING);
    }

    public function __toString()
    {
        $str = $this->getTypeName().' ';
        $period = $this->getPeriod();

        if ($period->getYear()) {
            $str .= '/' . $period->getYear();
        }
        if ($period->getQuarter()) {
            $str .= ' T' . $period->getQuarter();
        }

        return $str;
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRate(): float
    {
        return $this->getRevenue() > 0
            ? round($this->getTax() * 100 / $this->getRevenue(), 2)
            : 0;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        $typeName = array_flip(self::TYPES);
        if (!isset($typeName[$this->type])) {
            return null;
        }

        return $typeName[$this->type];
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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        $statusName = array_flip(self::STATUSES);
        if (!isset($statusName[$this->status])) {
            return null;
        }

        return $statusName[$this->status];
    }

    public function getPayedAt(): ?\DateTimeInterface
    {
        return $this->payedAt;
    }

    public function setPayedAt(?\DateTimeInterface $payedAt): self
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
                $invoices = array_merge($invoices, $period->getInvoices()->toArray());
            }
            return $invoices;
        }

        return $this->getPeriod()->getInvoices()->toArray();
    }

    public function setInvoices(?array $invoices)
    {
        return $this;
    }

}
