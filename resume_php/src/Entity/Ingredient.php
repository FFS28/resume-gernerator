<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=IngredientRepository::class)
 */
class Ingredient
{
    const TYPE_MEAT = "meat"; // Viandes
    const TYPE_FISH_SEAFOOD = "fish_seafood"; // Poissons et fruits de mer
    const TYPE_FRUIT_VEGETABLE_MUSHROOM   = "fruit_vegetable_mushroom"; // Fruits et légumes
    const TYPE_CEREAL_LEGUME = "cereal_legume"; // Céréales et Légumineuses
    const TYPE_ANIMAL_FAT = "animal_fat"; // Matière grasse animale
    const TYPE_VEGETABLE_FAT  = "vegetable_fat"; // Matière grasse végétale
    const TYPE_YEAST  = "yeast"; // Levures
    const TYPE_AROMATIC_HERB  = "aromatic_herb"; // Herbes aromatiques
    const TYPE_SPICES  = "spice"; // Epices
    const TYPE_SUGAR  = "sugar"; // Sucres
    const TYPE_SALT  = "salt"; // Sels
    const TYPE_ALCOHOL  = "alcohol"; // Alcool
    const TYPE_WATER  = "water"; // Eau

    /** @var array user friendly named type */
    const TYPES = [
        self::TYPE_MEAT => self::TYPE_MEAT,
        self::TYPE_FISH_SEAFOOD => self::TYPE_FISH_SEAFOOD,
        self::TYPE_FRUIT_VEGETABLE_MUSHROOM => self::TYPE_FRUIT_VEGETABLE_MUSHROOM,
        self::TYPE_CEREAL_LEGUME => self::TYPE_CEREAL_LEGUME,
        self::TYPE_ANIMAL_FAT => self::TYPE_ANIMAL_FAT,
        self::TYPE_VEGETABLE_FAT => self::TYPE_VEGETABLE_FAT,
        self::TYPE_YEAST => self::TYPE_YEAST,
        self::TYPE_AROMATIC_HERB => self::TYPE_AROMATIC_HERB,
        self::TYPE_SPICES => self::TYPE_SPICES,
        self::TYPE_SUGAR => self::TYPE_SUGAR,
        self::TYPE_SALT => self::TYPE_SALT,
        self::TYPE_ALCOHOL => self::TYPE_ALCOHOL,
        self::TYPE_WATER => self::TYPE_WATER,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $type;

    /**
     * @ORM\OneToMany(targetEntity=RecipeIngredient::class, mappedBy="ingredient", orphanRemoval=true)
     */
    private $recipeIngredients;

    public function __construct()
    {
        $this->recipeIngredients = new ArrayCollection();
    }

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

    public function __toString(): ?string
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

    public function getType(): ?string
    {
        return $this->type;
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

    public function setType(?string $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getIsVege(): ?bool
    {
        return $this->type !== self::TYPE_MEAT
            && $this->type !== self::TYPE_FISH_SEAFOOD;
    }

    public function getIsVegan(): ?bool
    {
        return $this->getIsVege()
            && $this->type !== self::TYPE_ANIMAL_FAT;
    }

    /**
     * @return Collection|RecipeIngredient[]
     */
    public function getRecipeIngredients(): Collection
    {
        return $this->recipeIngredients;
    }

    public function addRecipeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if (!$this->recipeIngredients->contains($recipeIngredient)) {
            $this->recipeIngredients[] = $recipeIngredient;
            $recipeIngredient->setIngredient($this);
        }

        return $this;
    }

    public function removeRecipeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if ($this->recipeIngredients->contains($recipeIngredient)) {
            $this->recipeIngredients->removeElement($recipeIngredient);
            // set the owning side to null (unless already changed)
            if ($recipeIngredient->getIngredient() === $this) {
                $recipeIngredient->setIngredient(null);
            }
        }

        return $this;
    }
}
