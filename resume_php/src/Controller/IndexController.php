<?php

namespace App\Controller;

use App\Form\Type\ContactFormType;
use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\LinkRepository;
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

class IndexController extends AbstractController
{
    /**
     * @Route("/", name="index")
     * @param Request $request
     * @param AttributeRepository $attributeRepository
     * @param SkillRepository $skillRepository
     * @param ExperienceRepository $experienceRepository
     * @param EducationRepository $educationRepository
     * @param HobbyRepository $hobbyRepository
     * @param LinkRepository $linkRepository
     * @param MailerInterface $mailer
     * @param TranslatorInterface $translator
     * @return Response
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function index(
        Request $request,
        AttributeRepository $attributeRepository,
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        EducationRepository $educationRepository,
        HobbyRepository $hobbyRepository,
        LinkRepository $linkRepository,
        MailerInterface $mailer,
        TranslatorInterface $translator
    ) {
        $form = $this->createForm(ContactFormType::class);
        $form->handleRequest($request);

        $experiencesFilter = $request->query->get('all') ? [] : ['onHomepage' => true];
        $data = [
            'attributes' => $attributeRepository->findAllIndexedBy('slug', false),
            'attributes_listable' => $attributeRepository->findAllIndexedBy('slug', true),
            'skills' => $skillRepository->findBy(['onHomepage' => true], ['level' => 'DESC']),
            'experiences' => $experienceRepository->findBy($experiencesFilter, ['dateBegin' => 'DESC']),
            'educations' => $educationRepository->findBy([], ['dateBegin' => 'DESC']),
            'hobbies' => $hobbyRepository->findAll(),
            'links' => $linkRepository->findAll(),
            'css' => '',
            'isPdf' => $request->query->get('pdf') ? true : false,
            'isDoc' => $request->query->get('doc') ? true : false,
            'contactForm' => $form->createView(),
            'filename' => 'jeremy-achain-cv',
            'messageSended' => $request->get('messageSended')
        ];

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from($this->getParameter('MAILER_FROM'))
                ->to($this->getParameter('MAILER_FROM'))
                ->replyTo($data['email'])
                ->subject($this->getParameter('MAILER_SUBJECT') . ' ' . $translator->trans('New message'))
                ->text($data['message']);

            $mailer->send($email);

            return $this->redirectToRoute('index', ['messageSended' => true]);
        }

        $data['isSubmittedWithErrors'] = $form->isSubmitted() && !$form->isValid();

        return $this->render('page/index.html.twig', $data);
    }
}
