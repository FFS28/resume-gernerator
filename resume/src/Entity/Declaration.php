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
     * @ORM\OneToMany(targetEntity="App\Entity\Invoice", mappedBy="declaration")
     */
    private $invoices;

    const NON_COMMERCIALE = 0.22;
    const CFP = 0.2;

    public function __construct()
    {
        $this->invoices = new ArrayCollection();
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

    /**
     * @return int[]
     */
    public static function getDueQuarterMonth()
    {
        return [
            4,
            7,
            10,
            1
        ];
    }

    public static function getAccountingYear(\DateTime $date): int
    {
        // @TODO Faux !!! A faire : si 04/06/2019 => 2019, si 12/01/2020 => 2019
        return intval($date->format('Y'));
    }

    /**
     * @param \DateTime $date
     * @return array
     * @throws \Exception
     */
    public static function getDueQuarterDatesBy(\DateTime $date): array
    {
        $dueDatesMonth = self::getDueQuarterMonth();
        $dueDates = [];

        foreach ($dueDatesMonth as $index => $dueDateMonth) {
            $dueDateBegin = new \DateTime(self::getAccountingYear($date).'-'.($dueDateMonth < 10 ? '0' : '').$dueDateMonth.'-01');
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
    public static function getNextQuarterDueDate(): array
    {
        $currentDate = new \DateTime();
        $dueDates = self::getDueQuarterDatesBy($currentDate);

        foreach ($dueDates as $index => $dueDate)
        {
            if ($currentDate < $dueDate[2]) {
                $dueDate[] = $currentDate > $dueDate[1] && $currentDate < $dueDate[2];
                return $dueDate;
            }
        }
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
        return round($this->getTax() * 100 / $this->getRevenue(), 2);
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

    /**
     * @return Collection|Invoice[]
     */
    public function getInvoices(): Collection
    {
        return $this->invoices;
    }

    public function addInvoice(Invoice $invoice): self
    {
        if (!$this->invoices->contains($invoice)) {
            $this->invoices[] = $invoice;
            $invoice->setDeclaration($this);
        }

        return $this;
    }

    public function removeInvoice(Invoice $invoice): self
    {
        if ($this->invoices->contains($invoice)) {
            $this->invoices->removeElement($invoice);
            // set the owning side to null (unless already changed)
            if ($invoice->getDeclaration() === $this) {
                $invoice->setDeclaration(null);
            }
        }

        return $this;
    }
}
