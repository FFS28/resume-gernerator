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
        $recipeSerialized['recipeIngredients'] = [];

        foreach ($recipe->orderedIngredientsByType() as $recipeIngredient) {
            $recipeIngredientSerialized = $recipeIngredient->toArray();
            $recipeIngredientSerialized['ingredient']['typeName'] = $translator->trans($recipeIngredientSerialized['ingredient']['typeName']);

            $recipeSerialized['recipeIngredients'][] = $recipeIngredientSerialized;
        }

        return $recipeSerialized;
    }

    /**
     * @Route("/kitchen", name="recipes")
     * @return Response
     */
    public function recipes(
        TranslatorInterface $translator,
        RecipeRepository $recipeRepository,
        IngredientRepository $ingredientRepository
    ) {
        $recipes = $recipeRepository->findAll();
        $ingredients = $ingredientRepository->findAll();
        $recipesSerialized = [];
        $ingredientsSerialized = [];

        foreach ($recipes as $recipe) {
            $recipesSerialized[] = $this->recipeToArray($recipe, $translator);
        }
        foreach ($ingredients as $ingredient) {
            $ingredientSerialized = $ingredient->toArray();
            $ingredientSerialized['typeName'] = $translator->trans($ingredientSerialized['typeName']);
            $ingredientsSerialized[] = $ingredientSerialized;
        }

        $data = [
            'recipes' => $recipesSerialized,
            'ingredients' => $ingredientsSerialized,
        ];

        return $this->render('project/recipes.html.twig', $data);
    }

    /**
     * @Route("/kitchen/{id}", name="recipe")
     * @return Response
     */
    public function recipe(Recipe $recipe, TranslatorInterface $translator) {
        $recipeSerialized = $this->recipeToArray($recipe, $translator);

        $data = [
            'recipe' => $recipeSerialized
        ];

        return $this->render('project/recipe.html.twig', $data);
    }
}
