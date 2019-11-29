<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Invoice;
use App\Service\InvoiceService;
use Doctrine\ORM\EntityManager;
use Konekt\PdfInvoice\InvoicePrinter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class InvoiceController extends EasyAdminController
{
    /** @var InvoiceService */
    public $invoiceService;

    public function __construct(InvoiceService $invoiceService)
    {
        $this->invoiceService = $invoiceService;
    }

    /**
     * @Route("/admin/invoice/{id}/pdf", name="dashboard")
     * @ParamConverter("invoice", class="App:Invoice")
     */
    public function pdf(Invoice $invoice)
    {
        return new Response(
            $this->invoiceService->getOrCreatePdf($invoice),
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
}
