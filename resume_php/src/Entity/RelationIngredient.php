<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\KitchenIngredientRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Doctrine\ORM\Mapping\MappedSuperclass;

/** @MappedSuperclass */
class RelationIngredient
{
    const UNIT_GRAM   = "g";
    const UNIT_KILOGRAM   = "kg";
    const UNIT_MILLILITER   = "ml";
    const UNIT_CENTILITER   = "cl";
    const UNIT_LITER   = "l";
    const UNIT_TABLESPOON   = "c-à-s";
    const UNIT_TEASPOON   = "c-à-c";

    /** @var array user friendly named type */
    const UNITS = [
        self::UNIT_GRAM => self::UNIT_GRAM,
        self::UNIT_KILOGRAM => self::UNIT_KILOGRAM,
        self::UNIT_MILLILITER => self::UNIT_MILLILITER,
        self::UNIT_CENTILITER => self::UNIT_CENTILITER,
        self::UNIT_LITER => self::UNIT_LITER,
        self::UNIT_TABLESPOON => self::UNIT_TABLESPOON,
        self::UNIT_TEASPOON => self::UNIT_TEASPOON,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity=Ingredient::class)
     * @ORM\JoinColumn(nullable=false)
     */
    protected $ingredient;

    /**
     * @ORM\Column(type="decimal", scale=1, nullable=true)
     */
    protected $quantity;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $unit;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $measure;

    public function toArray(): array
    {
        $encoder    = new JsonEncoder();
        $defaultContext = [
            AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER => function ($object, $format, $context) {
                return $object;
            },
        ];
        $normalizer = new ObjectNormalizer(null, null, null, null, null, null, $defaultContext);

        $serializer = new Serializer([$normalizer], [$encoder]);
        return json_decode($serializer->serialize($this, 'json'), true);
    }

    public function __toString(): string
    {
        $str = $this->getIngredient();

        if ($this->getMeasureStr()) {
            $str .= ' (' . $this->getMeasureStr() . ')';
        }

        return $str;
    }

    public function getName(): string {
        return $this->__toString();
    }

    public function getMeasureStr(): string
    {
        $str = '';
        if ($this->getMeasure() || $this->getQuantity()) {
            if ($this->getQuantity()) {
                $str .= $this->getQuantity() == 0.5 ? '1/2' : $this->getQuantity();
                if ($this->getMeasure() || $this->getUnit()) {
                    $str .= ' ';
                    if ($this->getMeasure()) {
                        $str .= $this->getMeasure();
                        if ($this->getQuantity() > 1) {
                            $str .= 's';
                        }
                    } else if ($this->getUnit()) {
                        $str .= $this->getUnit();
                    }
                }
            }
        }
        return $str;
    }

    public function getEquivalentGram(): ?int
    {
        $quantity = $this->getQuantity() ? $this->getQuantity() : 1;

        switch ($this->getMeasure()) {
            case 'pincée':
                return $quantity;
        }

        switch ($this->getUnit()) {
            case self::UNIT_TABLESPOON:
                return $quantity * 15;

            case self::UNIT_TEASPOON:
                return $quantity * 5;

            case self::UNIT_CENTILITER:
                return $quantity * 10;

            case self::UNIT_KILOGRAM:
            case self::UNIT_LITER:
                return $quantity * 1000;

            case self::UNIT_GRAM:
            default:
                return $quantity;
        }
    }

    /**
     * @return string
     */
    public function getUnitName()
    {
        $unitName = array_flip(self::UNITS);
        if (!isset($unitName[$this->unit])) {
            return null;
        }

        return $unitName[$this->unit];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIngredient(): ?Ingredient
    {
        return $this->ingredient;
    }

    public function setIngredient(?Ingredient $ingredient): self
    {
        $this->ingredient = $ingredient;

        return $this;
    }

    public function getQuantity(): ?float
    {
        return $this->quantity;
    }

    public function setQuantity(?float $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    public function getUnit(): ?string
    {
        return $this->unit;
    }

    public function setUnit(?string $unit): self
    {
        $this->unit = $unit;

        return $this;
    }

    public function getMeasure(): ?string
    {
        return $this->measure;
    }

    public function setMeasure(?string $measure): self
    {
        $this->measure = $measure;

        return $this;
    }
}
