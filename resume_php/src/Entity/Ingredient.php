<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\IngredientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=IngredientRepository::class)
 */
class Ingredient
{
    const TYPE_FRUIT_VEGETABLE   = "fruit vegetable"; // Fruits et légumes
    const TYPE_CEREAL_LEGUME = "cereal legume"; // Céréales et Légumineuses
    const TYPE_MEAT = "meat"; // Viandes
    const TYPE_FISH_SEAFOOD = "fish seafood"; // Poissons et fruits de mer
    const TYPE_ANIMAL_FAT = "animal fat"; // Matière grasse animale
    const TYPE_VEGETABLE_FAT  = "vegetable fat"; // Matière grasse végétale
    const TYPE_SUGAR  = "sugar"; // Sucres
    const TYPE_SALT  = "salt"; // Sels
    const TYPE_SPICES  = "spice"; // Epices
    const TYPE_AROMATIC_HERB  = "aromatic herb"; // Herbes aromatiques
    const TYPE_WATER  = "water"; // Eau
    const TYPE_ALCOHOL  = "alcohol"; // Alcool

    /** @var array user friendly named type */
    const TYPES = [
        self::TYPE_FRUIT_VEGETABLE => self::TYPE_FRUIT_VEGETABLE,
        self::TYPE_CEREAL_LEGUME => self::TYPE_CEREAL_LEGUME,
        self::TYPE_MEAT => self::TYPE_MEAT,
        self::TYPE_FISH_SEAFOOD => self::TYPE_FISH_SEAFOOD,
        self::TYPE_ANIMAL_FAT => self::TYPE_ANIMAL_FAT,
        self::TYPE_VEGETABLE_FAT => self::TYPE_VEGETABLE_FAT,
        self::TYPE_SUGAR => self::TYPE_SUGAR,
        self::TYPE_SALT => self::TYPE_SALT,
        self::TYPE_SPICES => self::TYPE_SPICES,
        self::TYPE_AROMATIC_HERB => self::TYPE_AROMATIC_HERB,
        self::TYPE_WATER => self::TYPE_WATER,
        self::TYPE_ALCOHOL => self::TYPE_ALCOHOL,
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
