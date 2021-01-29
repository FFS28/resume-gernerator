<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\Type\ContactFormType;
use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
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
    /**
     * @Route("/kitchen", name="recipes")
     * @return Response
     */
    public function recipes(RecipeRepository $recipeRepository, TranslatorInterface $translator) {
        $recipes = $recipeRepository->findAll();
        $recipesSerialized = [];

        foreach ($recipes as $recipe) {
            $recipeArray = $recipe->toArray();
            foreach ($recipeArray['recipeIngredients'] as &$recipeIngredient) {
                $recipeIngredient['ingredient']['typeName'] = $translator->trans($recipeIngredient['ingredient']['typeName']);
            }

            $recipesSerialized[] = $recipeArray;
        }

        $data = [
            'recipes' => $recipesSerialized
        ];

        return $this->render('project/recipes.html.twig', $data);
    }

    /**
     * @Route("/kitchen/{id}", name="recipe")
     * @return Response
     */
    public function recipe(Recipe $recipe, TranslatorInterface $translator) {
        $recipeSerialized = $recipe->toArray();
        foreach ($recipeSerialized['recipeIngredients'] as &$recipeIngredient) {
            $recipeIngredient['ingredient']['typeName'] = $translator->trans($recipeIngredient['ingredient']['typeName']);
        }

        $data = [
            'recipe' => $recipeSerialized
        ];

        return $this->render('project/recipe.html.twig', $data);
    }
}
