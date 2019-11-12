<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 */
class Invoice
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="invoices")
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\Column(type="date")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $payedAt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $totalHt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $object;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $tjm;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $payedBy;

    const PAYEDBY_CHECK    = "Chèque";
    const PAYEDBY_TRANSFERT = "Virement";

    /** @var array user friendly named type */
    protected static $payedByName = [
        self::PAYEDBY_CHECK => 'Chèque',
        self::PAYEDBY_TRANSFERT => 'Virement',
    ];

    public function __construct()
    {
        $this->tjm = 400;
        $this->createdAt = new \DateTime();
        $this->object = "Prestation de développement web - " . (new \DateTime())->format('Y-m');
        $this->setNumber((new \DateTime())->format('Y-m-'));
    }

    public function __toString(): string
    {
        return $this->getNumber();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?string
    {
        return $this->number;
    }

    public function setNumber(string $number): self
    {
        $this->number = $number;

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

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
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

    public function getTotalHt(): ?string
    {
        return $this->totalHt;
    }

    public function setTotalHt(string $totalHt): self
    {
        $this->totalHt = $totalHt;

        return $this;
    }

    public function getObject(): ?string
    {
        return $this->object;
    }

    public function setObject(?string $object): self
    {
        $this->object = $object;

        return $this;
    }

    public function getTjm(): ?int
    {
        return $this->tjm;
    }

    public function setTjm(?int $tjm): self
    {
        $this->tjm = $tjm;

        return $this;
    }

    /**
     * @return string
     */
    public function getPayedByName()
    {
        if (!isset(static::$payedByName[$this->payedBy])) {
            return null;
        }

        return static::$payedByName[$this->payedBy];
    }

    /**
     * @return array<string>
     */
    public static function getAvailablePayedBy()
    {
        return array_keys(static::$payedByName);
    }

    public function getPayedBy(): ?string
    {
        return $this->payedBy;
    }

    public function setPayedBy(?string $payedBy): self
    {
        $this->payedBy = $payedBy;

        return $this;
    }
}
