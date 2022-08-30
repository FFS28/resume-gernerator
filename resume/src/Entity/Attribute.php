<?php

namespace App\Entity;

use App\Repository\AttributeRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: AttributeRepository::class)]
#[UniqueEntity('slug')]
class Attribute implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, length: 255, unique: true)]
    private ?string $slug = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $icon = null;

    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
    private ?string $value = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $weight = null;

    #[ORM\Column(type: Types::BOOLEAN, nullable: true)]
    private ?bool $isListable = null;

    public function __construct()
    {
        $this->isListable = false;
    }

    public function __toString(): string
    {
        return $this->getSlug();
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getIcon(): ?string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    public function getValue(): ?string
    {
        return $this->value;
    }

    public function setValue(string $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function getWeight(): ?int
    {
        return $this->weight;
    }

    public function setWeight(int $weight): self
    {
        $this->weight = $weight;

        return $this;
    }

    public function getIsListable(): ?bool
    {
        return $this->isListable;
    }

    public function setIsListable(bool $isListable): self
    {
        $this->isListable = $isListable;

        return $this;
    }
}
