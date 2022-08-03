<?php

namespace App\Entity;

use App\Enum\OperationTypeEnum;
use App\Repository\OperationFilterRepository;
use DateTimeInterface;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Contracts\Translation\TranslatorInterface;

#[UniqueEntity(fields: ['name', 'date', 'amount'], message: 'A filter with same name already exists')]
#[ORM\Entity(repositoryClass: OperationFilterRepository::class)]
class OperationFilter implements \Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $name = null;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: OperationTypeEnum::class)]
    private ?OperationTypeEnum $type = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $target = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $label = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?DateTimeInterface $date = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2, nullable: true)]
    private ?string $amount = null;

    public ?string $labelAutocomplete = '';
    public ?string $labelCustom = '';

    #[ORM\Column(nullable: true)]
    private ?bool $hasDuplicate = null;

    public function __toString(): string
    {
        return $this->getName();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getType(): ?OperationTypeEnum
    {
        return $this->type;
    }

    public function setType(?OperationTypeEnum $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getTypeName(): ?string
    {
        return $this->type->toString();
    }

    public function getTarget(): ?string
    {
        return $this->target;
    }

    public function setTarget(?string $target): self
    {
        $this->target = $target;

        return $this;
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getDate(): ?DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(?DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(?string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function isHasDuplicate(): ?bool
    {
        return $this->hasDuplicate;
    }

    public function setHasDuplicate(?bool $hasDuplicate): self
    {
        $this->hasDuplicate = $hasDuplicate;

        return $this;
    }
}
