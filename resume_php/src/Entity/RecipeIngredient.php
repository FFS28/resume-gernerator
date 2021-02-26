<?php

namespace App\Entity;

use App\Repository\RecipeIngredientRepository;
use ArrayAccess;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @ORM\Entity(repositoryClass=RecipeIngredientRepository::class)
 */
class RecipeIngredient extends RelationIngredient
{
    /**
     * @ORM\ManyToOne(targetEntity=Recipe::class, inversedBy="recipeIngredients")
     * @ORM\JoinColumn(nullable=true)
     */
    private $recipe;

    public function getRecipe(): ?Recipe
    {
        return $this->recipe;
    }

    public function setRecipe(?Recipe $recipe): self
    {
        $this->recipe = $recipe;

        return $this;
    }
}
