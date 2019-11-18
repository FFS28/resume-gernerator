<?php

namespace App\Controller;

use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\LinkRepository;
use App\Repository\SkillRepository;
use Dompdf\Dompdf;
use PDFShift\PDFShift;
use Spatie\Browsershot\Browsershot;
use Spipu\Html2Pdf\Html2Pdf;
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
        LinkRepository $linkRepository
    )
    {
        $data = $this->loadData($attributeRepository, $skillRepository, $experienceRepository,
            $educationRepository, $hobbyRepository, $linkRepository);
        $data['isPdf'] = true;

        $pdfFilename = 'jeremy-achain-cv.pdf';

        //$url = $this->generateUrl('index', ['isPdf' => true], UrlGeneratorInterface::ABSOLUTE_URL);
        $html =  $this->renderView('page/index.html.twig', $data);

        /*$body_begin = strpos($html, '<body>') + 6;
        $body_end = strpos($html, '</body>');
        $body = substr($html, $body_begin, strlen($html) - $body_end - $body_begin );

        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML(file_get_contents($this->getParameter('kernel.project_dir') . '/public/build/css/app.css'), \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($body);
        return $mpdf->Output();*/

        /*$dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();$dompdf->stream();*/

        /*$html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($html);*/

        //return Browsershot::html('<h1>Hello world!!</h1>')->save('example.pdf');

        return new Response(
            //$snappyPdf->getOutput($url),
            //$snappyPdf->getOutputFromHtml($html),
            //$html2pdf->output(),
        null,
            200,
            array(
                'Content-Type'          => 'application/pdf',
                'Content-Disposition'   => 'inline; filename="'.$pdfFilename.'"'
                //'Content-Disposition'   => 'attachment; filename="'.$pdfFilename.'"'
            )
        );
    }
}
