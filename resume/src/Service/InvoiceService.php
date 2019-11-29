<?php

namespace App\Service;

use App\Entity\Invoice;
use DateInterval;
use Konekt\PdfInvoice\InvoicePrinter;

class InvoiceService
{
    public $pdfFileDirectory;
    public $companyName;
    public $companyStreet;
    public $companyCity;
    public $companySiret;
    public $companyApe;
    public $companyStatut;
    public $companyTva;

    public function __construct(
        string $pdfFileDirectory,
        string $companyName,
        string $companyStreet,
        string $companyCity,
        string $companySiret,
        string $companyApe,
        string $companyStatut,
        string $companyTva
    )
    {
        $this->pdfFileDirectory = $pdfFileDirectory;
        $this->companyName = $companyName;
        $this->companyStreet = $companyStreet;
        $this->companyCity = $companyCity;
        $this->companySiret = $companySiret;
        $this->companyApe = $companyApe;
        $this->companyStatut = $companyStatut;
        $this->companyTva = $companyTva;
    }

    /**
     * @param Invoice $invoice
     * @return InvoicePrinter
     * @throws \Exception
     */
    public function createPdf(Invoice $invoice)
    {
        $pdfInvoice = new InvoicePrinter('A4', '€', 'fr');

        /* Header settings */
        $pdfInvoice->setColor("#007fff");      // pdf color scheme
        $pdfInvoice->setType("Facture n° " . $invoice->getNumber());    // Invoice Type
        $pdfInvoice->setNumberFormat(',', ' ');
        $pdfInvoice->setReference($invoice->getNumber());   // Reference
        $pdfInvoice->setDate($invoice->getCreatedAt()->format('d/m/Y'));   //Billing Date
        $pdfInvoice->setDue($invoice->getCreatedAt()->add(new DateInterval('P1M'))->format('t/m/Y'));   //Billing Date

        $pdfInvoice->setFrom([
            utf8_decode($this->companyName),
            '',
            utf8_decode($this->companyStreet),
            utf8_decode($this->companyCity)
        ]);
        $pdfInvoice->setTo([
            utf8_decode($invoice->getCompany()->getName()),
            '',
            utf8_decode($invoice->getCompany()->getStreet()),
            utf8_decode($invoice->getCompany()->getPostalCode() . ' ' . $invoice->getCompany()->getCity())
        ]);

        $pdfInvoice->addItem(
            "Journée de prestation",
            "",
            $invoice->getDaysCount(),
            $invoice->getTotalTax() ? (Invoice::TAX_MULTIPLIER * 100)."%" : '',
            $invoice->getTjm(),
            '',
            $invoice->getTotalHt()
        );
        $pdfInvoice->AliasNbPages('');
        $pdfInvoice->addTotal("Total HT", $invoice->getTotalHt());
        $pdfInvoice->addTotal("TVA ".(Invoice::TAX_MULTIPLIER * 100)."%", $invoice->getTotalTax());
        $pdfInvoice->addTotal("Total TTC", $invoice->getTotalTtc(), true);

        $pdfInvoice->addTitle("Règlement");

        $pdfInvoice->addParagraph("
            RIB : 10278 07374 00020438301 93
            IBAN : FR76 1027 8073 7400 0204 3830 193
            BIC : CMCIFR2A
        ");

        $pdfInvoice->addTitle("Informations légales");

        $legalInformations = "
            Taux des pénalités en cas de retard de paiement : taux directeur de refinancement de la BCE, majoré de 10 points
            En cas de retard de paiement, indemnité forfaitaire légale pour frais de recouvrement : 40,00 €
            Escompte en cas de paiement anticipé : aucun
          
            Dispensé d'immatriculation au Registre du Commerce et des Sociétés et au Répertoire des Métiers";

        if(!$invoice->getTotalTax()) {
            $legalInformations = "
            TVA non applicable, art. 293B du CGI
            ${legalInformations}";
        }

        $pdfInvoice->addParagraph($legalInformations);

        $footerInformations = [
            $this->companyName,
            'Siret : ' . $this->companySiret,
            'APE : ' . $this->companyApe,
            $this->companyStatut,
            'N° TVA : ' . $this->companyTva
        ];

        $pdfInvoice->setFooternote(utf8_decode(implode(' - ', $footerInformations)));

        return $pdfInvoice;
    }

    /**
     * @param Invoice $invoice
     * @return string
     * @throws \Exception
     */
    public function getOrCreatePdf(Invoice $invoice, $force = false): string
    {
        if (!$force && file_exists($this->pdfFileDirectory.$invoice->getFilename())) {
            return file_get_contents($this->pdfFileDirectory.$invoice->getFilename());
        }

        $this->createPdf($invoice)->render($this->pdfFileDirectory.$invoice->getFilename(), 'F');

        return file_get_contents($this->pdfFileDirectory.$invoice->getFilename());
    }
}