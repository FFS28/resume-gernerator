<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\JsonResponse;

class RecipeController extends EasyAdminController
{
    /**
     * @param Recipe $recipe
     */
    protected function persistEntity($recipe)
    {
        $this->update($recipe);
        parent::persistEntity($recipe);
    }

    /**
     * @param Recipe $recipe
     */
    protected function updateEntity($recipe)
    {
        $this->update($recipe);
        parent::updateEntity($recipe);
    }

    /**
     * @param Recipe $recipe
     */
    private function update($recipe)
    {
        foreach ($recipe->getRecipeIngredients() as $recipeIngredient){
            $recipeIngredient->setRecipe($recipe);
        }
    }
}
