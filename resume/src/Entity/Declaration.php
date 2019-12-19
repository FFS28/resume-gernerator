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
     * @ORM\Column(type="integer")
     */
    private $year;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $quarter;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $month;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
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
    protected static $typeName = [
        self::TYPE_TVA => 'TVA',
        self::TYPE_SOCIAL => 'Social',
        self::TYPE_IMPOT => 'Impot',
    ];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Period", inversedBy="declarations")
     */
    private $period;

    const SOCIAL_NON_COMMERCIALE = 0.22;
    const SOCIAL_CFP = 0.02;

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
    protected static $statusName = [
        self::STATUS_WAITING => 'Waiting',
        self::STATUS_PAYED => 'Payed',
    ];

    public function __construct()
    {
        $this->setStatus(self::STATUS_WAITING);
    }

    public function __toString()
    {
        $str = $this->getTypeName().' '.$this->getYear();
        if ($this->getMonth()) {
            $str .= '/' . $this->getMonth();
        } elseif ($this->getQuarter()) {
            $str .= ' T' . $this->getQuarter();
        }

        return $str;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getYear(): ?int
    {
        return $this->year;
    }

    public function setYear(int $year): self
    {
        $this->year = $year;

        return $this;
    }

    public function getQuarter(): ?int
    {
        return $this->quarter;
    }

    public function setQuarter(?int $quarter): self
    {
        $this->quarter = $quarter;

        return $this;
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(?int $month): self
    {
        $this->month = $month;

        return $this;
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
        if (!isset(static::$typeName[$this->type])) {
            return null;
        }

        return static::$typeName[$this->type];
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
        if (!isset(static::$statusName[$this->status])) {
            return null;
        }

        return static::$statusName[$this->status];
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
        return $this->getPeriod()
            ? $this->getPeriod()->getInvoices()->toArray()
            : [];
    }

    public function setInvoices(?array $invoices)
    {
        return $this;
    }

}
