<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Recipe;
use Symfony\Component\HttpFoundation\JsonResponse;

class IngredientController extends EasyAdminController
{
    public function autocompleteAction()
    {
        dump($this->request->query->get('query'));
        $results = $this->get('easyadmin.autocomplete')->find(
            $this->request->query->get('entity'),
            $this->request->query->get('query'),
            $this->request->query->get('page', 1)
        );

        return new JsonResponse($results);
    }
}
