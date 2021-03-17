<?php

namespace App\Controller;

use App\Entity\KitchenIngredient;
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
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\Translator;
use Symfony\Contracts\Translation\TranslatorInterface;

class KitchenController extends AbstractController
{
    /**
     * @param Recipe $recipe
     * @param $translator
     * @param KitchenIngredient[] $kitchenIngredients
     * @return array
     */
    private function recipeToArray(Recipe $recipe, $translator, $kitchenIngredients = []): array
    {
        $recipeSerialized = $recipe->toArray();
        $recipeSerialized['imagePath'] = '/' . $this->getParameter('RECIPE_DIRECTORY') . $recipeSerialized['image'];
        $recipeSerialized['recipeIngredients'] = [];

        $recipeIngredients = $recipe->orderedIngredientsByType();
        $countIngredientInKitchen = 0;

        foreach ($recipeIngredients as $recipeIngredient) {
            $recipeIngredientSerialized = $recipeIngredient->toArray();
            $recipeIngredientSerialized['kitchen'] = false;
            $recipeIngredientSerialized['ingredient']['typeName'] = $translator->trans($recipeIngredientSerialized['ingredient']['typeName']);

            if (array_key_exists($recipeIngredient->getIngredient()->getId(), $kitchenIngredients)) {
                $kitchenIngredient = $kitchenIngredients[$recipeIngredient->getIngredient()->getId()];

                if (!$kitchenIngredient->getUnit() && !$kitchenIngredient->getMeasure() && !$kitchenIngredient->getQuantity()
                    || $kitchenIngredient->getUnit() && $recipeIngredient->getUnit() && $recipeIngredient->getEquivalentGram() <= $kitchenIngredient->getEquivalentGram()
                    || $kitchenIngredient->getMeasure() && $kitchenIngredient->getMeasure() === $recipeIngredient->getMeasure() && $recipeIngredient->getQuantity() <= $kitchenIngredient->getQuantity()
                    || !$kitchenIngredient->getUnit() && !$kitchenIngredient->getMeasure() && $recipeIngredient->getQuantity() < $kitchenIngredient->getQuantity()
                ) {
                    $recipeIngredientSerialized['kitchen'] = true;
                    $countIngredientInKitchen++;
                }
            }
            $recipeSerialized['recipeIngredients'][] = $recipeIngredientSerialized;
        }

        $recipeSerialized['kitchen'] = $countIngredientInKitchen . '/' . count($recipeIngredients);
        $recipeSerialized['allKitchen'] = $countIngredientInKitchen === count($recipeIngredients);
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
        $kitchenIngredientsById = [];
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

        foreach ($kitchenIngredients as $kitchenIngredient) {
            $kitchenIngredientSerialized = $kitchenIngredient->toArray();
            $kitchenIngredientSerialized['ingredient']['typeName'] = $translator->trans($kitchenIngredientSerialized['ingredient']['typeName']);

            $kitchenIngredientsSerialized[] = $kitchenIngredientSerialized;
            $kitchenIngredientsById[$kitchenIngredient->getIngredient()->getId()] = $kitchenIngredient;
        }
        foreach ($recipes as $recipe) {
            $recipeSerialized = $this->recipeToArray($recipe, $translator, $kitchenIngredientsById);
            $recipesSerialized[] = $recipeSerialized;
        }
        foreach ($ingredients as $ingredient) {
            $ingredientSerialized = $ingredient->toArray();
            $ingredientSerialized['typeName'] = $translator->trans($ingredientSerialized['typeName']);
            $ingredientsSerialized[] = $ingredientSerialized;
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

    /**
     * @Route("/kitchen/{slug}/image", name="recipe_image")
     * @return Response
     */
    public function recipeImage(Recipe $recipe, KernelInterface $kernel) {
        $file = $kernel->getProjectDir() . '/public/' . $this->getParameter('RECIPE_DIRECTORY') . $recipe->getImage();
        $headers = array(
            'Content-Type'     => 'image/jpg',
            'Content-Disposition' => 'inline; filename="'.$recipe->getImage().'"');
        return new BinaryFileResponse($file, 200, $headers);
    }
}
