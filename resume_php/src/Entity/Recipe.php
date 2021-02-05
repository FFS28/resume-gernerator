<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use App\Repository\RecipeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ApiResource()
 * @ORM\Entity(repositoryClass=RecipeRepository::class)
 * @Vich\Uploadable
 */
class Recipe
{
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
     * @ORM\Column(type="string", length=100, unique=true, nullable=true)
     * @Gedmo\Slug(fields={"name"})
     */
    private $slug;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nbSlices;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $cookingDuration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $preparationDuration;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $waitingDuration;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $instructions = [];

    /**
     * @ORM\OneToMany(targetEntity=RecipeIngredient::class, mappedBy="recipe", orphanRemoval=true)
     */
    private $recipeIngredients;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @var string
     */
    private $image;

    /**
     * @Vich\UploadableField(mapping="recipes", fileNameProperty="image")
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @var \DateTime
     */
    private $updatedAt;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $source;

    public function __construct()
    {
        $this->recipeIngredients = new ArrayCollection();
    }

    public function __toString(): ?string
    {
        return $this->getName();
    }

    public function isVege(): ?bool
    {
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_MEAT ||
                $recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_FISH_SEAFOOD) {
                return false;
            }
        }
        return true;
    }

    public function isVegan(): ?bool
    {
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_MEAT ||
                $recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_FISH_SEAFOOD ||
                $recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_ANIMAL_FAT) {
                return false;
            }
        }
        return true;
    }

    public function isMeat(): ?bool
    {
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_MEAT) {
                return true;
            }
        }
        return false;
    }

    public function isFish(): ?bool
    {
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_FISH_SEAFOOD) {
                return true;
            }
        }
        return false;
    }

    public function isSweet(): ?bool
    {
        $sugar = 0;
        $salt = 0;
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()->isSalty() === true) {
                return false;
            } elseif ($recipeIngredient->getIngredient()->isSweet() === true) {
                return true;
            } elseif ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_SUGAR) {
                $sugar += $recipeIngredient->getEquivalentGram();
            } elseif ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_SALT) {
                $salt += $recipeIngredient->getEquivalentGram();
            }

        }
        return $sugar > $salt;
    }

    public function isSalty(): ?bool
    {
        $sugar = 0;
        $salt = 0;
        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            if ($recipeIngredient->getIngredient()->isSalty() === true) {
                return true;
            } elseif ($recipeIngredient->getIngredient()->isSweet() === true) {
                return false;
            } elseif ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_SUGAR) {
                $sugar += $recipeIngredient->getEquivalentGram();
            } elseif ($recipeIngredient->getIngredient()->getType() === Ingredient::TYPE_SALT) {
                $salt += $recipeIngredient->getEquivalentGram();
            }
        }
        return $salt > $sugar;
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

    public function getIngredientIds(): array
    {
        $ids = [];

        /** @var RecipeIngredient $recipeIngredient */
        foreach ($this->recipeIngredients as $recipeIngredient) {
            $ids[] = $recipeIngredient->getIngredient()->getId();
        }

        return $ids;
    }

    public function orderedIngredientsByType(): array {
        /** @var RecipeIngredient[] $recipeIngredients */
        $recipeIngredients = $this->getRecipeIngredients()->toArray();

        usort($recipeIngredients,
            function($recipeIngredientA, $recipeIngredientB) {
                $a = array_search($recipeIngredientA->getIngredient()->getType(), array_values(Ingredient::TYPES));
                $b = array_search($recipeIngredientB->getIngredient()->getType(), array_values(Ingredient::TYPES));

                if ($a == $b) {
                    $a = $recipeIngredientA->getQuantity();
                    $b = $recipeIngredientB->getQuantity();

                    if ($a == $b) {
                        return 0;
                    }
                    return ($a > $b) ? -1 : 1;
                }
                return ($a > $b) ? 1 : -1;
        });

        return $recipeIngredients;
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

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function getNbSlices(): ?int
    {
        return $this->nbSlices;
    }

    public function setNbSlices(?int $nbSlices): self
    {
        $this->nbSlices = $nbSlices;

        return $this;
    }

    public function getCookingDuration(): ?int
    {
        return $this->cookingDuration;
    }

    public function setCookingDuration(?int $cookingDuration): self
    {
        $this->cookingDuration = $cookingDuration;

        return $this;
    }

    public function getPreparationDuration(): ?int
    {
        return $this->preparationDuration;
    }

    public function setPreparationDuration(?int $preparationDuration): self
    {
        $this->preparationDuration = $preparationDuration;

        return $this;
    }

    public function getInstructions(): ?array
    {
        return array_values($this->instructions);
    }

    public function setInstructions(?array $instructions): self
    {
        $this->instructions = array_values($instructions);

        return $this;
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
            $recipeIngredient->setRecipe($this);
        }

        return $this;
    }

    public function removeRecipeIngredient(RecipeIngredient $recipeIngredient): self
    {
        if ($this->recipeIngredients->contains($recipeIngredient)) {
            $this->recipeIngredients->removeElement($recipeIngredient);
            // set the owning side to null (unless already changed)
            if ($recipeIngredient->getRecipe() === $this) {
                $recipeIngredient->setRecipe(null);
            }
        }

        return $this;
    }

    public function getWaitingDuration(): ?int
    {
        return $this->waitingDuration;
    }

    public function setWaitingDuration(?int $waitingDuration): self
    {
        $this->waitingDuration = $waitingDuration;

        return $this;
    }

    public function setImageFile(File $image = null)
    {
        $this->imageFile = $image;

        // VERY IMPORTANT:
        // It is required that at least one field changes if you are using Doctrine,
        // otherwise the event listeners won't be called and the file is lost
        if ($image) {
            // if 'updatedAt' is not defined in your entity, use another property
            $this->updatedAt = new \DateTime('now');
        }
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getImageFile()
    {
        return $this->imageFile;
    }

    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getSource(): ?string
    {
        return $this->source;
    }

    public function setSource(?string $source): self
    {
        $this->source = $source;

        return $this;
    }
}
