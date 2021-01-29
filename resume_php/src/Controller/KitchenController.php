<?php

namespace App\Controller;

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
use Symfony\Contracts\Translation\TranslatorInterface;

class KitchenController extends AbstractController
{
    /**
     * @Route("/kitchen", name="kitchen")
     * @return Response
     */
    public function index(RecipeRepository $recipeRepository) {
        $recipes = $recipeRepository->findAll();
        $recipesSerialized = [];

        foreach ($recipes as $recipe) {
            $recipesSerialized[] = $recipe->toArray();
        }

        $data = [
            'recipes' => $recipesSerialized
        ];

        return $this->render('project/kitchen.html.twig', $data);
    }
}
