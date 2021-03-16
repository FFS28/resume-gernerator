<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Recipe;
use App\Form\Type\RecipeIngredientType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\FormInterface;
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

    }
}
