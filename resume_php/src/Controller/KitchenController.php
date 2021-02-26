<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Entity\RecipeIngredient;
use App\Form\Type\ContactFormType;
use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\IngredientRepository;
use App\Repository\KitchenIngredientRepository;
use App\Repository\LinkRepository;
use App\Repository\RecipeRepository;
use App\Repository\SkillRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class KitchenController extends AbstractController
{
    private function recipeToArray(Recipe $recipe, $translator): array
    {
        $recipeSerialized = $recipe->toArray();
        $recipeSerialized['imagePath'] = '/' . $this->getParameter('RECIPE_DIRECTORY') . $recipeSerialized['image'];
        $recipeSerialized['recipeIngredients'] = [];

        foreach ($recipe->orderedIngredientsByType() as $recipeIngredient) {
            $recipeIngredientSerialized = $recipeIngredient->toArray();
            $recipeIngredientSerialized['ingredient']['typeName'] = $translator->trans($recipeIngredientSerialized['ingredient']['typeName']);

            $recipeSerialized['recipeIngredients'][] = $recipeIngredientSerialized;
        }

        return $recipeSerialized;
    }

    /**
     * @Route("/kitchen/recipes", name="recipes")
     * @return Response
     */
    public function recipes(
        TranslatorInterface $translator,
        RecipeRepository $recipeRepository,
        IngredientRepository $ingredientRepository,
        KitchenIngredientRepository $kitchenIngredientRepository
    ) {
        $recipes = $recipeRepository->findAll();
        $ingredients = $ingredientRepository->findAll();
        $kitchenIngredients = $kitchenIngredientRepository->findAll();
        $recipesSerialized = [];
        $ingredientsSerialized = [];
        $kitchenIngredientsSerialized = [];

        /**
         * @var Recipe $recipeA
         * @var Recipe $recipeB
         */
        usort($recipes, function($recipeA, $recipeB){
            $sortA = 1;
            $sortB = 1;

            if ($recipeA->isSalty()) $sortA = 3;
            if ($recipeB->isSalty()) $sortB = 3;
            if ($recipeA->isSweet()) $sortA = 2;
            if ($recipeB->isSweet()) $sortB = 2;

            if ($sortA === $sortB) {
                if ($recipeA->isvegan()) $sortA = 4;
                if ($recipeB->isvegan()) $sortB = 4;
                if ($recipeA->isVege()) $sortA = 3;
                if ($recipeB->isVege()) $sortB = 3;
                if ($recipeA->isMeat()) $sortA = 2;
                if ($recipeB->isMeat()) $sortB = 2;
                if ($recipeA->isFish()) $sortA = 1;
                if ($recipeB->isFish()) $sortB = 1;
            }

            return $sortB - $sortA;
        });

        foreach ($recipes as $recipe) {
            $recipesSerialized[] = $this->recipeToArray($recipe, $translator);
        }
        foreach ($ingredients as $ingredient) {
            $ingredientSerialized = $ingredient->toArray();
            $ingredientSerialized['typeName'] = $translator->trans($ingredientSerialized['typeName']);
            $ingredientsSerialized[] = $ingredientSerialized;
        }
        foreach ($kitchenIngredients as $kitchenIngredient) {
            $kitchenIngredientSerialized = $kitchenIngredient->toArray();
            $kitchenIngredientSerialized['ingredient']['typeName'] = $translator->trans($kitchenIngredientSerialized['ingredient']['typeName']);

            $kitchenIngredientsSerialized[] = $kitchenIngredientSerialized;
        }

        $data = [
            'recipes' => $recipesSerialized,
            'ingredients' => $ingredientsSerialized,
            'kitchen' => $kitchenIngredientsSerialized,
        ];

        return $this->json($data);
    }

    /**
     * @Route("/kitchen", name="kitchen")
     * @return Response
     */
    public function kitchen() {
        return $this->render('project/kitchen/findAll.html.twig', []);
    }

    /**
     * @Route("/kitchen/{slug}", name="recipe")
     * @return Response
     */
    public function recipe(Recipe $recipe, TranslatorInterface $translator) {
        $recipeSerialized = $this->recipeToArray($recipe, $translator);

        $data = [
            'recipe' => $recipeSerialized
        ];

        return $this->render('project/kitchen/findOne.html.twig', $data);
    }
}
