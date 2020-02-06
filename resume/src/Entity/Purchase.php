<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PurchaseRepository")
 * @Vich\Uploadable
 */
class Purchase
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $number;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $payedAt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $totalHt;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2, nullable=true)
     */
    private $totalTax;

    /**
     * @ORM\Column(type="string", length=255)
     * @var string
     */
    private $proof;

    /**
     * @ORM\Column(type="datetime")
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @Vich\UploadableField(mapping="purchases", fileNameProperty="proof")
     * @var File
     */
    private $proofFile;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Period", inversedBy="purchases")
     */
    private $period;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $proofData = [];

    public function __construct()
    {
        $this->setPayedAt(new \DateTime());
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumber(): ?int
    {
        return $this->number;
    }

    public function setNumber(int $number): self
    {
        $this->number = $number;

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

    public function setTotalHt(?string $totalHt): self
    {
        $this->totalHt = $totalHt;

        return $this;
    }

    public function getTotalTax(): ?string
    {
        return $this->totalTax;
    }

    public function setTotalTax(?string $totalTax): self
    {
        $this->totalTax = $totalTax;

        return $this;
    }

    public function getTotalTtc(): ?string
    {
        return $this->getTotalHt() + $this->getTotalTax();
    }

    public function setProofFile(File $proof = null)
    {
        $this->proofFile = $proof;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($proof) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getProofFile()
    {
        return $this->proofFile;
    }

    public function setProof($proof)
    {
        $this->proof = $proof;
    }

    public function getProof()
    {
        return $this->proof;
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

    public function getProofData(): ?array
    {
        return $this->proofData;
    }

    public function setProofData(?array $proofData): self
    {
        $this->proofData = $proofData;

        return $this;
    }
}
