<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Invoice;
use App\Service\InvoiceService;
use Doctrine\ORM\EntityManager;
use http\Client\Request;
use Konekt\PdfInvoice\InvoicePrinter;
use Psr\Http\Message\RequestInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvoiceController extends EasyAdminController
{
    /** @var InvoiceService */
    private $invoiceService;

    /** @var MailerInterface */
    private $mailer;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(InvoiceService $invoiceService,
                                MailerInterface $mailer,
                                TranslatorInterface $translator)
    {
        $this->invoiceService = $invoiceService;
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    /**
     * @Route("/admin/invoice/{id}/pdf", name="invoice")
     * @ParamConverter("invoice", class="App:Invoice")
     */
    public function pdf(Invoice $invoice)
    {
        return new Response(
            $this->invoiceService->getOrCreatePdf($invoice, true),
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $invoice->getFilename() . '"'
            )
        );
    }

    public function pdfAction()
    {
        $id = $this->request->query->get('id');
        /** @var Invoice $entity */
        $entity = $this->em->getRepository(Invoice::class)->find($id);

        return $this->pdf($entity);
    }

    public function validateAction()
    {
        $id = $this->request->query->get('id');
        /** @var Invoice $entity */
        $entity = $this->em->getRepository(Invoice::class)->find($id);

        $entity->setStatus(Invoice::STATUS_PAYED);
        $entity->setPayedAt(new \DateTime('now'));

        $this->em->flush();

        return $this->redirectToReferrer();
    }

    public function sendAction()
    {
        $id = $this->request->query->get('id');
        /** @var Invoice $entity */
        $entity = $this->em->getRepository(Invoice::class)->find($id);

        if ($entity->getFilename() && $entity->getCompany()->getEmail()) {

            $email = (new Email())
                ->from($this->getParameter('MAILER_FROM'))
                ->to($this->getParameter('MAILER_FROM'))
                //->to($entity->getCompany()->getEmail())
                ->subject($this->getParameter('MAILER_SUBJECT') . ' ' .
                    $this->translator->trans('Invoice') . ' n° ' . $entity->getNumber())
                ->text('')
                ->attachFromPath(
                    $this->getParameter('PDF_DIRECTORY').$entity->getFilename(),
                    'invoice-'.$entity->getNumber());

            $this->mailer->send($email);
        }

        return $this->redirectToReferrer();
    }
}
