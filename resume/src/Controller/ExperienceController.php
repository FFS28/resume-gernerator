<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Experience;
use App\Form\Type\ContactCompaniesType;
use App\Repository\ExperienceRepository;
use App\Service\ExperienceService;
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

class ExperienceController extends EasyAdminController
{
    /** @var DeclarationService */
    private $experienceService;

    public function __construct(ExperienceService $ExperienceService)
    {
        $this->experienceService = $ExperienceService;
    }

    public function updateBatchAction()
    {
        $form = $this->request->request->get('batch_form');
        $this->experienceService->update(explode(',', $form['ids']));
    }
    
}
