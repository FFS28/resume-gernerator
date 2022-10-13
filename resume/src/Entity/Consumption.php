<?php

namespace App\Entity;

use App\Repository\ConsumptionRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[UniqueEntity(fields: ['date'], message: 'Consumption already exists')]
#[ORM\Entity(repositoryClass: ConsumptionRepository::class)]
class Consumption implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(inversedBy: 'consumptions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?ConsumptionMonth $consumptionMonth = null;

    #[ORM\Column]
    private ?int $day = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $meterLowHour = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $meterFullHour = null;

    #[ORM\Column(type: Types::BIGINT)]
    private ?string $meterWeekendHour = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $diffLowHour = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $diffFullHour = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $diffWeekendHour = null;

    #[ORM\Column(type: Types::BIGINT, nullable: true)]
    private ?string $diffTotal = null;

    public function __toString(): string
    {
        return $this->getDate()->format('d/m/Y');
    }

    public function getConsumptionMonth(): ?ConsumptionMonth
    {
        return $this->consumptionMonth;
    }

    public function setConsumptionMonth(?ConsumptionMonth $consumptionMonth): self
    {
        $this->consumptionMonth = $consumptionMonth;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        $this->day = intval($date->format('d'));

        return $this;
    }

    public function getDay(): ?int
    {
        return $this->day;
    }

    public function setDay(int $day): self
    {
        $this->day = $day;

        return $this;
    }

    public function getMeterLowHour(): ?string
    {
        return $this->meterLowHour;
    }

    public function setMeterLowHour(string $meterLowHour): self
    {
        $this->meterLowHour = $meterLowHour;

        return $this;
    }

    public function getMeterFullHour(): ?string
    {
        return $this->meterFullHour;
    }

    public function setMeterFullHour(string $meterFullHour): self
    {
        $this->meterFullHour = $meterFullHour;

        return $this;
    }

    public function getMeterWeekendHour(): ?string
    {
        return $this->meterWeekendHour;
    }

    public function setMeterWeekendHour(string $meterWeekendHour): self
    {
        $this->meterWeekendHour = $meterWeekendHour;

        return $this;
    }

    public function getDiffLowHour(): ?string
    {
        return $this->diffLowHour;
    }

    public function getLowHourMegaWatt(): ?int
    {
        return intval($this->getDiffLowHour()) / 1000;
    }

    public function setDiffLowHour(string $diffLowHour): self
    {
        $this->diffLowHour = $diffLowHour;

        return $this;
    }

    public function getDiffFullHour(): ?string
    {
        return $this->diffFullHour;
    }

    public function getFullHourMegaWatt(): ?int
    {
        return round(intval($this->getDiffFullHour()) / 1000, 1);
    }

    public function setDiffFullHour(string $diffFullHour): self
    {
        $this->diffFullHour = $diffFullHour;

        return $this;
    }

    public function getDiffWeekendHour(): ?string
    {
        return $this->diffWeekendHour;
    }

    public function getWeekendHourMegaWatt(): ?int
    {
        return round(intval($this->getDiffWeekendHour()) / 1000, 1);
    }

    public function setDiffWeekendHour(string $diffWeekendHour): self
    {
        $this->diffWeekendHour = $diffWeekendHour;

        return $this;
    }

    public function getDiffTotal(): ?string
    {
        return $this->diffTotal;
    }

    public function getTotalMegaWatt(): ?int
    {
        return round(intval($this->getDiffTotal()) / 1000, 1);
    }

    public function setDiffTotal(string $diffTotal): self
    {
        $this->diffTotal = $diffTotal;

        return $this;
    }
}
