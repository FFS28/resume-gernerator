<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class UserController extends AbstractController
{
    #[Route(path: '/login', name: 'login')]
    public function login(AuthenticationUtils $authenticationUtils, FormFactoryInterface $formFactory) : Response
    {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $form = $formFactory
            ->createNamedBuilder('login')
            ->setAction('/login')
            ->add('_username', null, ['label' => 'Username'])
            ->add('_password', PasswordType::class, ['label' => 'Password'])
            ->add('_password', PasswordType::class, ['label' => 'Password'])
            ->add('submit', SubmitType::class, ['label' => 'Connexion', 'attr' => ['class' => 'btn-primary btn-block']])
            ->getForm();

        return $this->render('security/login.html.twig', [
            'mainNavLogin' => true, 'title' => 'Connexion',
            'form' => $form->createView(),
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }
}
