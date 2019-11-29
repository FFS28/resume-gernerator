<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass="App\Repository\InvoiceRepository")
 * @UniqueEntity("number")
 */
class Invoice
{
    /**
     * @ORM\Id()', default: true , role: ROLE_USER_LIST }
      - { entity: 'UsersManagement', label: 'Members management', icon: 'user' , role: ROLE_USER_ALL }
      - { label: 'Meetings' }
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $number;

    const NUMBER_DATE_FORMAT = 'Yn-';

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Company", inversedBy="invoices", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Experience", inversedBy="invoices", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $experience;

    /**
     * @ORM\Column(type="date")
     */
    private $createdAt;

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

    const TAX_MULTIPLIER = 0.2;

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

    const PAYEDBY_CHECK    = "check";
    const PAYEDBY_TRANSFERT = "transfert";

    /** @var array user friendly named type */
    protected static $payedByName = [
        self::PAYEDBY_CHECK => 'Check',
        self::PAYEDBY_TRANSFERT => 'Transfert',
    ];

    const STATUS_DRAFT = 'draft';
    const STATUS_WAITING = 'waiting';
    const STATUS_PAYED = 'payed';

    /**
     * @ORM\Column(type="string", length=255, nullable=true))
     */
    private $status;

    /** @var array user friendly named type */
    protected static $statusName = [
        self::STATUS_DRAFT => 'Draft',
        self::STATUS_WAITING => 'Waiting',
        self::STATUS_PAYED => 'Payed',
    ];

    const DUE_INTERVAL_1M = 'P1M';

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $dueInterval;

    /** @var array user friendly named type */
    protected static $dueIntervalName = [
        self::DUE_INTERVAL_1M => '30 days end of month',
    ];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Activity", mappedBy="invoice")
     */
    private $activities;

    const TJM_DEFAULT = 400;
    const LIMIT_AE_TVA = 33200;
    const LIMIT_AE = 70000;

    public function __construct()
    {
        $this->tjm = self::TJM_DEFAULT;
        $this->createdAt = new \DateTime();
        $this->object = "Prestation de développement web - " . (new \DateTime())->format('Y-m');
        $this->setNumber((new \DateTime())->format('Y-m-'));
        $this->setPayedBy(self::PAYEDBY_TRANSFERT);
        $this->status = self::STATUS_DRAFT;
        $this->totalTax = 0;
        $this->activities = new ArrayCollection();
        $this->dueInterval = 'P1M';
    }

    /**
     * @param Activity[] $activities
     */
    public function importActivities(array $activities)
    {
        $dayCount = 0;

        foreach ($activities as $activity) {
            $activity->setInvoice($this);
            $dayCount += $activity->getValue();
        }

        $this->setTotalHt($dayCount * $this->getTjm());
    }

    public function getFilename(): string
    {
        return $this->getNumber();
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

    public function getExperience(): ?Experience
    {
        return $this->experience;
    }

    public function setExperience(?Experience $experience): self
    {
        $this->experience = $experience;

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

    public function getCreatedAtYear(): int
    {
        return $this->createdAt->format('Y');
    }

    public function getPayedAtYear(): int
    {
        return $this->payedAt->format('Y');
    }

    public function getCreatedAtQuarter(): int
    {
        return ceil($this->createdAt->format('n') / 3);
    }

    public function getPayedAtQuarter(): int
    {
        return ceil($this->payedAt->format('n') / 3);
    }

    public function getTotalHt(): ?string
    {
        return $this->totalHt;
    }

    public function getTotalTtc(): ?string
    {
        return $this->totalHt + $this->getTotalTax();
    }

    public function setTotalHt(string $totalHt): self
    {
        $this->totalHt = $totalHt;

        return $this;
    }

    public function getDaysCount(): ?float
    {
        return $this->getTjm() && $this->getTjm() !== null && $this->getTjm() > 0
            ? $this->getTotalHt() / $this->getTjm()
            : null;
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

    public function getPayedBy(): ?string
    {
        return $this->payedBy;
    }

    public function setPayedBy(?string $payedBy): self
    {
        $this->payedBy = $payedBy;

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

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return string
     */
    public function getStatusName()
    {
        if (!isset(static::$statusName[$this->status])) {
            return null;
        }

        return static::$statusName[$this->status];
    }

    /**
     * @return array<string>
     */
    public static function getAvailableStatus()
    {
        return array_keys(static::$statusName);
    }

    /**
     * @return array<string>
     */
    public static function getStatusList()
    {
        return array_flip(static::$statusName);
    }

    /**
     * @return Collection|Activity[]
     */
    public function getActivities(): Collection
    {
        return $this->activities;
    }

    public function addActivity(Activity $activity): self
    {
        if (!$this->activities->contains($activity)) {
            $this->activities[] = $activity;
            $activity->setInvoice($this);
        }

        return $this;
    }

    public function removeActivity(Activity $activity): self
    {
        if ($this->activities->contains($activity)) {
            $this->activities->removeElement($activity);
            // set the owning side to null (unless already changed)
            if ($activity->getInvoice() === $this) {
                $activity->setInvoice(null);
            }
        }

        return $this;
    }

    public function getDueInterval(): ?string
    {
        return $this->dueInterval;
    }

    public function setDueInterval(?string $dueInterval): self
    {
        $this->dueInterval = $dueInterval;

        return $this;
    }
}
