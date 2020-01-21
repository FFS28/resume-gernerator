<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Company;
use App\Form\Type\ContactCompaniesType;
use App\Repository\CompanyRepository;
use App\Service\CompanyService;
use App\Service\DeclarationService;
use App\Service\InvoiceService;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Konekt\PdfInvoice\InvoicePrinter;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class CompanyController extends EasyAdminController
{
    /** @var DeclarationService */
    private $companyService;

    public function __construct(CompanyService $companyService)
    {
        $this->companyService = $companyService;
    }

    /**
     * @Route("/admin/company/contact/{slug?}", name="company_contact")
     * @param CompanyRepository $companyRepository
     * @param TranslatorInterface $translator
     * @param Request $request
     * @param EntityManagerInterface $entityManager
     * @param int $year
     * @param int $month
     * @param Company|null $company
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    public function contact(
        CompanyRepository $companyRepository,
        TranslatorInterface $translator,
        Request $request,
        EntityManagerInterface $entityManager,
        int $year = 0, int $month = 0, Company $company = null
    )
    {
        $viewData = [];

        $form = $this->createForm(ContactCompaniesType::class, null, []);
        $form->handleRequest($request);
        $viewData['contactForm'] = $form->createView();

        if ($form->isSubmitted() && $form->isValid()) {
            $formData = $form->getData();

            return $this->redirectToRoute('easyadmin', ['entity'=> 'Company', 'action'=> 'list']);
        }

        return $this->render('page/contact_companies.html.twig', $viewData);
    }
}