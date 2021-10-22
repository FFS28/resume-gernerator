<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Asset\Packages;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/login", name="login")
     * @param Request $request
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(
        Request $request,
        AuthenticationUtils $authenticationUtils
    ) {
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();

        $form = $this->get('form.factory')
            ->createNamedBuilder('login')
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

    /*public function login(Request $request): PassportInterface
    {
        $password = $request->request->get('password');
        $username = $request->request->get('username');

        return new Passport(
            new UserBadge($username), // Badge pour transporter l'user
            new PasswordCredentials($password), // Badge pour transporter le password
            [new CsrfTokenBadge('login', $csrfToken)] // Badge pour transporter un token CSRF
        );
    }*/
}
