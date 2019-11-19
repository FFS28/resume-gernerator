<?php

namespace App\Controller;

use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\LinkRepository;
use App\Repository\SkillRepository;
use Knp\Snappy\AbstractGenerator;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class IndexController extends AbstractController
{
    private function loadData(
        AttributeRepository $attributeRepository,
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        EducationRepository $educationRepository,
        HobbyRepository $hobbyRepository,
        LinkRepository $linkRepository
    )
    {
        return [
            'attributes' => $attributeRepository->findAllIndexedBy('slug'),
            'attributes_exclude' => ['name', 'quote', 'job', 'subtitle', 'description'],
            'skills' => $skillRepository->findBy(['onHomepage' => true], ['level' => 'DESC']),
            'experiences' => $experienceRepository->findBy(['onHomepage' => true], ['dateBegin' => 'DESC']),
            'educations' => $educationRepository->findBy([], ['dateBegin' => 'DESC']),
            'hobbies' => $hobbyRepository->findAll(),
            'links' => $linkRepository->findAll(),
        ];
    }

    /**
     * @Route("/", name="index")
     * @param AttributeRepository $attributeRepository
     * @param SkillRepository $skillRepository
     * @param ExperienceRepository $experienceRepository
     * @param EducationRepository $educationRepository
     * @param HobbyRepository $hobbyRepository
     * @param LinkRepository $linkRepository
     * @return Response
     */
    public function index(
        AttributeRepository $attributeRepository,
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        EducationRepository $educationRepository,
        HobbyRepository $hobbyRepository,
        LinkRepository $linkRepository)
    {
        $data = $this->loadData($attributeRepository, $skillRepository, $experienceRepository,
            $educationRepository, $hobbyRepository, $linkRepository);
        $data['isPdf'] = false;

        return $this->render('page/index.html.twig', $data);
    }

    /**
     * @Route("/pdf", name="pdf")
     * @param AttributeRepository $attributeRepository
     * @param SkillRepository $skillRepository
     * @param ExperienceRepository $experienceRepository
     * @param EducationRepository $educationRepository
     * @param HobbyRepository $hobbyRepository
     * @param LinkRepository $linkRepository
     * @return Response
     */
    public function pdf(
        AttributeRepository $attributeRepository,
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        EducationRepository $educationRepository,
        HobbyRepository $hobbyRepository,
        LinkRepository $linkRepository,
        Pdf $snappyPdf
    )
    {
        $data = $this->loadData($attributeRepository, $skillRepository, $experienceRepository,
            $educationRepository, $hobbyRepository, $linkRepository);
        $data['isPdf'] = true;

        $pdfFilename = 'jeremy-achain-cv.pdf';

        $html =  $this->renderView('page/index.html.twig', $data);

        return new Response(
            $snappyPdf->getOutputFromHtml($html),
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'inline; filename="'.$pdfFilename.'"'
                //'Content-Disposition'   => 'attachment; filename="'.$pdfFilename.'"'
            )
        );
    }
}
