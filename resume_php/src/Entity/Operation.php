<?php

namespace App\Entity;

use App\Repository\OperationRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(fields={"date", "name", "amount"}, message="Operation already exists")
 * @ORM\Entity(repositoryClass=OperationRepository::class)
 */
class Operation
{
    const TYPE_INCOME    = "income";
    const TYPE_REFUND    = "refund";
    const TYPE_SUPPLY    = "supply";
    const TYPE_FOOD = "food";
    const TYPE_CHARGE = "charge";
    const TYPE_SUBSCRIPTION  = "subscription";
    const TYPE_HOBBY  = "hobby";
    const TYPE_OTHER  = "other";
    const TYPE_HIDDEN  = "hidden";

    /** @var array user friendly named type */
    const TYPES = [
        'income' => self::TYPE_INCOME,
        'refund' => self::TYPE_REFUND,
        'supply' => self::TYPE_SUPPLY,
        'food' => self::TYPE_FOOD,
        'charge' => self::TYPE_CHARGE,
        'subscription' => self::TYPE_SUBSCRIPTION,
        'hobby' => self::TYPE_HOBBY,
        'other' => self::TYPE_OTHER,
        'hidden' => self::TYPE_HIDDEN,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="decimal", precision=10, scale=2)
     */
    private $amount;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $target;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $label;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    public function __construct()
    {

    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        $typeName = array_flip(self::TYPES);
        if (!isset($typeName[$this->type])) {
            return null;
        }

        return $typeName[$this->type];
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

        return $this;
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

    public function getAmount(): ?string
    {
        return $this->amount;
    }

    public function setAmount(string $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
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
        return $this->label ? $this->label : $this->name;
    }

    public function setLabel(?string $label): self
    {
        $this->label = $label;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }
}
