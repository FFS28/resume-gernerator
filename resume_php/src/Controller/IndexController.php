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
            'contactForm' => $form->createView(),
            'filename' => 'jeremy-achain-cv.pdf',
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

    /**
     * @Route("/projects/scales-of-me", name="project-scales")
     * @param Request $request
     * @return Response
     */
    public function scales(
        Request $request
    ) {
        $data = [
            'scales' => [
                [
                    'title' => 'Gender identity',
                    'children' => [
                        [
                            'labels' => ['agenre', 'non-binary', 'female'],
                            'value' => 20,
                            'description' => ''
                        ],
                        [
                            'labels' => ['agenre', 'non-binary', 'male'],
                            'value' => 80,
                            'description' => ''
                        ],
                    ]
                ],
                [
                    'title' => 'Gender expression',
                    'children' => [
                        [
                            'labels' => ['hyper-masc', 'andro, both', 'hyper-female'],
                            'value' => 20,
                            'description' => ''
                        ]
                    ]
                ],
                [
                    'title' => 'Sexual orientation',
                    'children' => [
                        [
                            'labels' => ['no female', 'female'],
                            'value' => 100,
                            'description' => ''
                        ],
                        [
                            'labels' => ['no male', 'male'],
                            'value' => 10,
                            'description' => ''
                        ],
                        [
                            'labels' => ['asexual', 'sexual', 'hypersexual'],
                            'value' => 70,
                            'description' => ''
                        ],
                        [
                            'labels' => ['vanilla', 'curious', 'all the BDSM'],
                            'value' => 50,
                            'description' => ''
                        ],
                    ]
                ],
                [
                    'title' => 'Romantic orientation',
                    'children' => [
                        [
                            'labels' => ['no female', 'female'],
                            'value' => 100,
                            'description' => ''
                        ],
                        [
                            'labels' => ['no male', 'male'],
                            'value' => 0,
                            'description' => ''
                        ],
                        [
                            'labels' => ['aromantic', 'romantic', 'extra-romantic'],
                            'value' => 70,
                            'description' => ''
                        ],
                    ]
                ],
                [
                    'title' => 'Relation orientation',
                    'children' => [
                        [
                            'labels' => ['mono', 'open, fluid, single', 'poly'],
                            'value' => 70,
                            'description' => ''
                        ]
                    ]
                ],
            ]
        ];

        return $this->render('project/scales.html.twig', $data);
    }
}
