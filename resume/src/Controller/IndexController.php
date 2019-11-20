<?php

namespace App\Controller;

use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\LinkRepository;
use App\Repository\SkillRepository;
use Knp\Bundle\SnappyBundle\Snappy\Response\PdfResponse;
use Knp\Snappy\AbstractGenerator;
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
     * @return Response
     */
    public function index(
        Request $request,
        AttributeRepository $attributeRepository,
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        EducationRepository $educationRepository,
        HobbyRepository $hobbyRepository,
        LinkRepository $linkRepository,
        Pdf $snappyPdf
    ) {
        $data = [
            'attributes' => $attributeRepository->findAllIndexedBy('slug'),
            'attributes_exclude' => ['name', 'quote', 'job', 'subtitle', 'description'],
            'skills' => $skillRepository->findBy(['onHomepage' => true], ['level' => 'DESC']),
            'experiences' => $experienceRepository->findBy([
                'onHomepage' => $request->query->get('all') ? false : true
            ], ['dateBegin' => 'DESC']),
            'educations' => $educationRepository->findBy([], ['dateBegin' => 'DESC']),
            'hobbies' => $hobbyRepository->findAll(),
            'links' => $linkRepository->findAll(),
        ];
        $data['isPdf'] = $request->query->get('pdf') ? true : false;

        if ($data['isPdf']) {
            $pdfFilename = 'jeremy-achain-cv.pdf';
            $html =  $this->renderView('page/index.html.twig', $data);

            /*return new Response(
                $html,
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'inline; filename="'.$pdfFilename.'"'
                    //'Content-Disposition'   => 'attachment; filename="'.$pdfFilename.'"'
                )
            );*/
        }

        return $this->render('page/index.html.twig', $data);
    }
}
