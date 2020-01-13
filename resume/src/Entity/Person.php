<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PersonRepository")
 */
class Person
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $civility;

    const CIVILITY_H    = "h";
    const CIVILITY_F    = "f";

    /** @var array user friendly named type */
    const CIVILITIES = [
        'M' => self::CIVILITY_H ,
        'Mme' => self::CIVILITY_F,
    ];

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $firstname;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $lastname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="simple_array", nullable=true)
     */
    private $phones = [];

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="persons", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isInvoicingDefault;

    public function __toString(): string
    {
        return $this->getCivilityName() . ' ' . $this->getFirstname(). ' ' . $this->getLastname();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCivility(): ?string
    {
        return $this->civility;
    }

    public function setCivility(?string $civility): self
    {
        $this->civility = $civility;

        return $this;
    }

    /**
     * @return string
     */
    public function getCivilityName()
    {
        $civilityNames = array_flip(self::CIVILITIES);
        if (!isset($civilityNames[$this->civility])) {
            return null;
        }

        return $civilityNames[$this->civility];
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

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
}
