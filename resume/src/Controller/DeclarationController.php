<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use App\Service\DeclarationService;
use App\Service\InvoiceService;
use Doctrine\ORM\EntityManager;
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

class DeclarationController extends EasyAdminController
{
    /** @var DeclarationService */
    private $declarationService;

    public function __construct(DeclarationService $declarationService) {
        $this->declarationService = $declarationService;
    }

    public function calculateAction()
    {
        $id = $this->request->query->get('id');
        /** @var Declaration $entity */
        $entity = $this->em->getRepository(Declaration::class)->find($id);

        $this->declarationService->calculate($entity);

        return $this->redirectToReferrer();
    }

    public function assignAction()
    {
        $id = $this->request->query->get('id');
        /** @var Declaration $entity */
        $entity = $this->em->getRepository(Declaration::class)->find($id);

        $this->declarationService->assign($entity);

        return $this->redirectToReferrer();
    }

    public function validateAction()
    {
        $id = $this->request->query->get('id');
        /** @var Declaration $entity */
        $entity = $this->em->getRepository(Declaration::class)->find($id);

        $entity->setStatus(Declaration::STATUS_PAYED);
        $entity->setPayedAt(new \DateTime('now'));

        $this->em->flush();

        return $this->redirectToReferrer();
    }
}
