<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\JsonResponse;

class RecipeIngredientController extends EasyAdminController
{
    public function autocompleteAction()
    {
        return new JsonResponse([
            "results" => []
        ]);
    }
}
