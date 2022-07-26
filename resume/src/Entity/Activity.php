<?php

namespace App\Entity;

use App\Repository\ActivityRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: ActivityRepository::class)]
class Activity implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 3, scale: 1, nullable: true)]
    private ?string $value = null;

    #[ORM\ManyToOne(targetEntity: Company::class, cascade: ['persist'], inversedBy: 'activities')]
    private ?Company $company = null;

    #[ORM\ManyToOne(targetEntity: Invoice::class, inversedBy: 'activities')]
    private ?Invoice $invoice = null;

    public function __toString(): string
    {
        return $this->getCompany() . ' - ' . $this->getValue() . ' - ' . $this->getDate()->format('d/m/Y');
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $Company): self
    {
        $this->company = $Company;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(?string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): self
    {
        $this->invoice = $invoice;

        return $this;
    }
}
