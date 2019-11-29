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
        $pdfFileDirectory = $this->getParameter('kernel.project_dir') . '/data/invoices/';
        $pdfFileName = $invoice->getFilename().'.pdf';

        if (file_exists($pdfFileDirectory.$pdfFileName)) {
            $pdfFileContent = file_get_contents($pdfFileDirectory.$pdfFileName);
        } else {
            $invoice = $this->invoiceService->createPdf($invoice);

            $pdfFileContent = $invoice->render($pdfFileName, 'I');
        }

        return new Response(
            $pdfFileContent,
            200,
            array(
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $pdfFileName . '"'
                //'Content-Disposition'   => 'attachment; filename="'.$pdfFilename.'"'
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
