<?php

namespace App\Entity;

use App\Repository\ConsumptionMonthRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: ConsumptionMonthRepository::class)]
class ConsumptionMonth implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?int $year = null;

    #[ORM\Column]
    private ?int $month = null;

    #[ORM\OneToMany(mappedBy: 'consumptionMonth', targetEntity: Consumption::class, orphanRemoval: true)]
    private Collection $consumptions;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $total = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $lowHour = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $fullHour = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $weekendHour = null;

    public function __construct()
    {
        $this->consumptions = new ArrayCollection();
    }

    public function __toString(): string
    {
        return str_pad($this->getMonth(), 2, 0, STR_PAD_LEFT) . '/' . $this->getYear();
    }

    public function getMonth(): ?int
    {
        return $this->month;
    }

    public function setMonth(int $month): self
    {
        $this->month = $month;

        return $this;
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

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection<int, Consumption>
     */
    public function getConsumptions(): Collection
    {
        return $this->consumptions;
    }

    public function addConsumption(Consumption $consumption): self
    {
        if (!$this->consumptions->contains($consumption)) {
            $this->consumptions->add($consumption);
            $consumption->setConsumptionMonth($this);
        }

        return $this;
    }

    public function removeConsumption(Consumption $consumption): self
    {
        if ($this->consumptions->removeElement($consumption)) {
            // set the owning side to null (unless already changed)
            if ($consumption->getConsumptionMonth() === $this) {
                $consumption->setConsumptionMonth(null);
            }
        }

        return $this;
    }

    public function getLowHourMegaWatt(): ?int
    {
        return round(intval($this->getLowHour()) / 1000);
    }

    public function getLowHour(): ?string
    {
        return $this->lowHour;
    }

    public function setLowHour(string $lowHour): self
    {
        $this->lowHour = $lowHour;

        return $this;
    }

    public function getFullHourMegaWatt(): ?int
    {
        return round(intval($this->getFullHour()) / 1000);
    }

    public function getFullHour(): ?string
    {
        return $this->fullHour;
    }

    public function setFullHour(string $fullHour): self
    {
        $this->fullHour = $fullHour;

        return $this;
    }

    public function getWeekendHourMegaWatt(): ?int
    {
        return round(intval($this->getWeekendHour()) / 1000);
    }

    public function getWeekendHour(): ?string
    {
        return $this->weekendHour;
    }

    public function setWeekendHour(string $weekendHour): self
    {
        $this->weekendHour = $weekendHour;

        return $this;
    }

    public function getTotal(): ?string
    {
        return $this->total;
    }

    public function getTotalMegaWatt(): ?int
    {
        return round(intval($this->getTotal()) / 1000);
    }

    public function setTotal(string $total): self
    {
        $this->total = $total;

        return $this;
    }
}
