<?php

namespace App\Entity;

use App\Enum\PersonCivilityEnum;
use App\Repository\PersonRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Stringable;

#[ORM\Entity(repositoryClass: PersonRepository::class)]
class Person implements Stringable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private int $id;

    #[ORM\Column(type: Types::STRING, nullable: true, enumType: PersonCivilityEnum::class)]
    private ?PersonCivilityEnum $civility = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $firstname = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $lastname = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $phones = [];

    #[ORM\ManyToOne(targetEntity: Company::class, cascade: ['persist'], inversedBy: 'persons')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    private ?bool $isInvoicingDefault = null;

    /**
     * @var string[]
     */
    #[ORM\Column(type: 'simple_array', nullable: true)]
    private array $emails = [];

    public function __toString(): string
    {
        return $this->getCivilityName() . ' ' . $this->getFirstname() . ' ' . $this->getLastname();
    }

    public function getCivilityName(): ?string
    {
        return $this->civility?->toString();
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): self
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): self
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivility(): ?PersonCivilityEnum
    {
        return $this->civility;
    }

    public function setCivility(?PersonCivilityEnum $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    public function getPhones(): ?array
    {
        return $this->phones;
    }

    public function setPhones(?array $phones): self
    {
        $this->phones = $phones;

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getIsInvoicingDefault(): ?bool
    {
        return $this->isInvoicingDefault;
    }

    public function setIsInvoicingDefault(bool $isInvoicingDefault): self
    {
        $this->isInvoicingDefault = $isInvoicingDefault;

        return $this;
    }

    public function getEmails(): ?array
    {
        return $this->emails;
    }

    public function setEmails(?array $emails): self
    {
        $this->emails = $emails;

        return $this;
    }
}
