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
            'attributes' => $attributeRepository->findAllIndexedBy('slug'),
            'attributes_exclude' => ['name', 'quote', 'job', 'subtitle', 'description'],
            'skills' => $skillRepository->findBy(['onHomepage' => true], ['level' => 'DESC']),
            'experiences' => $experienceRepository->findBy($experiencesFilter, ['dateBegin' => 'DESC']),
            'educations' => $educationRepository->findBy([], ['dateBegin' => 'DESC']),
            'hobbies' => $hobbyRepository->findAll(),
            'links' => $linkRepository->findAll(),
            'css' => '',
            'isPdf' => $request->query->get('pdf') ? true : false,
            'contactForm' => $form->createView()
        ];

        if ($data['isPdf']) {
            $pdfFilename = 'jeremy-achain-cv.pdf';
            $html =  $this->renderView('page/index.html.twig', $data);
            $pdf = null;

            /*if ($request->query->get('pdf') === 'mpdf') {
                $mpdf = new \Mpdf\Mpdf([
                    'default_font' => 'DejaVuSans'
                ]);
                //$mpdf->WriteHTML(file_get_contents(, \Mpdf\HTMLParserMode::HEADER_CSS);
                $mpdf->WriteHTML($html);
                $pdf = $mpdf->Output();
            }

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

            if ($request->query->get('pdf') === 'tcpdf') {
                $tcpdf = $tcpdfService->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
                $tcpdf->AddPage();
                $tcpdf->writeHTMLCell($w = 0, $h = 0, $x = '', $y = '', $html, $border = 0, $ln = 1, $fill = 0, $reseth = true, $align = '', $autopadding = true);
                $pdf = $tcpdf->Output($pdfFilename,'I');
            }

            if ($request->query->get('pdf') === 'browsershot') {
                $pdf = Browsershot::html($html)->pdf();
            }*/

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

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();

            $email = (new Email())
                ->from($this->getParameter('MAILER_FROM'))
                ->to($this->getParameter('MAILER_FROM'))
                ->replyTo($data['email'])
                ->subject($this->getParameter('MAILER_SUBJECT') . ' ' . $translator->trans('New message'))
                ->text($data['message']);

            $mailer->send($email);

            return $this->redirectToRoute('index');
        }

        return $this->render('page/index.html.twig', $data);
    }
}
