<?php

namespace App\Controller;

use App\Repository\AttributeRepository;
use App\Repository\EducationRepository;
use App\Repository\ExperienceRepository;
use App\Repository\HobbyRepository;
use App\Repository\LinkRepository;
use App\Repository\SkillRepository;
use Dompdf\Dompdf;
use Spatie\Browsershot\Browsershot;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use WhiteOctober\TCPDFBundle\Controller\TCPDFController;
use WhiteOctober\TCPDFBundle\WhiteOctoberTCPDFBundle;

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
     */
    public function index(
        Request $request,
        AttributeRepository $attributeRepository,
        SkillRepository $skillRepository,
        ExperienceRepository $experienceRepository,
        EducationRepository $educationRepository,
        HobbyRepository $hobbyRepository,
        LinkRepository $linkRepository,
        TCPDFController $tcpdfService
    ) {
        $experiencesFilter = $request->query->get('all') ? [] : ['onHomepage' => true];
        $data = [
            'attributes' => $attributeRepository->findAllIndexedBy('slug'),
            'attributes_exclude' => ['name', 'quote', 'job', 'subtitle', 'description'],
            'skills' => $skillRepository->findBy(['onHomepage' => true], ['level' => 'DESC']),
            'experiences' => $experienceRepository->findBy($experiencesFilter, ['dateBegin' => 'DESC']),
            'educations' => $educationRepository->findBy([], ['dateBegin' => 'DESC']),
            'hobbies' => $hobbyRepository->findAll(),
            'links' => $linkRepository->findAll(),
            'css' => ''
        ];
        $data['isPdf'] = $request->query->get('pdf') ? true : false;

        if ($data['isPdf']) {
            $pdfFilename = 'jeremy-achain-cv.pdf';
            $html =  $this->renderView('page/index.html.twig', $data);
            $data['css'] = file_get_contents($this->getParameter('kernel.project_dir') . '/public/build/css/index.css');
            $pdf = null;

            if ($request->query->get('pdf') === 'html2pdf') {
                $html2pdf = new Html2Pdf();
                $html2pdf->writeHTML($html);
                $pdf = $html2pdf->output();
            }

            if ($request->query->get('pdf') === 'dompdf') {
                $dompdf = new Dompdf();
                $dompdf->loadHtml($html);
                $dompdf->setPaper('A4', 'portrait');
                $dompdf->render();
                $pdf = $dompdf->output();
            }

            if ($request->query->get('pdf') === 'mpdf') {
                $mpdf = new \Mpdf\Mpdf();
                //$mpdf->WriteHTML(file_get_contents(, \Mpdf\HTMLParserMode::HEADER_CSS);
                $mpdf->WriteHTML($html);
                $pdf = $mpdf->Output();
            }

            if ($request->query->get('pdf') === 'tcpdf') {
                $tcpdf = $tcpdfService->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $tcpdf->AddPage();
                $tcpdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                $pdf = $tcpdf->Output($pdfFilename,'I');
            }

            if ($request->query->get('pdf') === 'browsershot') {
                $pdf = Browsershot::html($html)->pdf();
            }

            if ($pdf) {
                return new Response(
                    $pdf,
                    200,
                    array(
                        'Content-Type' => 'application/pdf',
                        'Content-Disposition' => 'inline; filename="' . $pdfFilename . '"'
                        //'Content-Disposition'   => 'attachment; filename="'.$pdfFilename.'"'
                    )
                );
            }
        }

        return $this->render('page/index.html.twig', $data);
    }
}
