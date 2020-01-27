<?php

namespace App\Service;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Repository\ExperienceRepository;
use App\Repository\InvoiceRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Konekt\PdfInvoice\InvoicePrinter;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

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

    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var InvoiceRepository */
    private $invoiceRepository;

    /** @var ExperienceRepository */
    private $experienceRepository;

    /** @var DeclarationService */
    private $declarationService;

    /** @var SerializerInterface */
    private $serializer;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(
        string $pdfFileDirectory,
        string $companyName,
        string $companyStreet,
        string $companyCity,
        string $companySiret,
        string $companyApe,
        string $companyStatut,
        string $companyTva,
        EntityManagerInterface $entityManager,
        InvoiceRepository $invoiceRepository,
        ExperienceRepository $experienceRepository,
        DeclarationService $declarationService,
        SerializerInterface $serializer,
        TranslatorInterface $translator
    ) {
        $this->pdfFileDirectory = $pdfFileDirectory;
        $this->companyName = $companyName;
        $this->companyStreet = $companyStreet;
        $this->companyCity = $companyCity;
        $this->companySiret = $companySiret;
        $this->companyApe = $companyApe;
        $this->companyStatut = $companyStatut;
        $this->companyTva = $companyTva;

        $this->entityManager = $entityManager;
        $this->invoiceRepository = $invoiceRepository;
        $this->experienceRepository = $experienceRepository;
        $this->declarationService = $declarationService;

        $this->serializer = $serializer;
        $this->translator = $translator;
    }

    private function encode(?string $string): string
    {
        if (!$string) return '';

        $fromEncoding = mb_detect_encoding($string);
        $toEncoding = 'UTF-8////IGNORE';

        return $convertedString = @mb_convert_encoding($string, $toEncoding, $fromEncoding) ?:
            @iconv($fromEncoding, $toEncoding, $string);
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
        $pdfInvoice->setColor("#5cb85c");      // pdf color scheme
        $pdfInvoice->setType($this->encode("Facture n° " . $invoice->getNumber()));    // Invoice Type
        $pdfInvoice->setNumberFormat(',', ' ');
        $pdfInvoice->setReference($invoice->getNumber());   // Reference
        $pdfInvoice->setDate($invoice->getCreatedAt()->format('d/m/Y'));   //Billing Date
        $pdfInvoice->setDue($invoice->getCreatedAt()->add(new DateInterval('P1M'))->format('t/m/Y'));   //Billing Date

        $pdfInvoice->setFrom([
            $this->encode($this->companyName),
            '',
            $this->encode($this->companyStreet),
            $this->encode($this->companyCity)
        ]);
        $pdfInvoice->setTo([
            $this->encode($invoice->getCompany()->getName()),
            '',
            $this->encode($invoice->getCompany()->getStreet()),
            $this->encode($invoice->getCompany()->getPostalCode() . ' ' . $invoice->getCompany()->getCity())
        ]);

        $pdfInvoice->addItem(
            $this->encode("Journée de prestation"),
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

        $pdfInvoice->addParagraph($this->encode("
            RIB : 10278 07374 00020438301 93
            IBAN : FR76 1027 8073 7400 0204 3830 193
            BIC : CMCIFR2A
        "));

        $pdfInvoice->addTitle($this->encode("Informations légales"));

        $legalInformations = "
            Taux des pénalités en cas de retard de paiement : taux directeur de refinancement de la BCE, majoré de 10 points
            En cas de retard de paiement, indemnité forfaitaire légale pour frais de recouvrement : 40,00 EUR
            Escompte en cas de paiement anticipé : aucun
          
            Dispensé d'immatriculation au Registre du Commerce et des Sociétés et au Répertoire des Métiers";

        if(!$invoice->getTotalTax()) {
            $legalInformations = "
            TVA non applicable, art. 293B du CGI
            ".$legalInformations;
        }

        $pdfInvoice->addParagraph($this->encode($legalInformations));

        $footerInformations = [
            $this->companyName,
            'Siret : ' . $this->companySiret,
            'APE : ' . $this->companyApe,
            $this->companyStatut,
            'Numero TVA : ' . $this->companyTva
        ];

        $pdfInvoice->setFooternote($this->encode(implode(' - ', $footerInformations)));

        return $pdfInvoice;
    }

    /**
     * @param Invoice $invoice
     * @param bool $force
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

    public function generateInvoicesBook(): string
    {
        $invoices = $this->invoiceRepository->findAll();

        $columns = ["Date d'encaissement",
            "Référence de la facture",
            "Nom du client",
            "Nature de la vente",
            "Montant de la vente",
            "Mode d'encaissement"];
        $dataCsv = [];

        foreach($invoices as $invoice) {
            $dataCsv[] = [
                $columns[0] => $invoice->getPayedAt() ? $invoice->getPayedAt()->format('d/m/Y') : '',
                $columns[1] => $invoice->getNumber(),
                $columns[2] => $invoice->getCompany()->getName(),
                $columns[3] => $invoice->getObject(),
                $columns[4] => $invoice->getTotalHt(),
                $columns[5] => $this->translator->trans($invoice->getPayedByName())
            ];
        }

        $filename = $this->pdfFileDirectory.'livre-recettes.csv';

        file_put_contents(
            $filename,
            $this->serializer->encode($dataCsv, 'csv', [
                'csv_delimiter' => ';'
            ])
        );

        return $filename;
    }

    public function calculTva(Invoice $invoice)
    {
        $isOutOfTaxLimit = $this->invoiceRepository->isOutOfTaxLimit($invoice->getTotalHt());

        if ($isOutOfTaxLimit) {
            $invoice->setTotalTax($invoice->getTotalHt() * Invoice::TAX_MULTIPLIER);
        }
    }

    public function calculTotalHt(Invoice $invoice)
    {
        $invoice->setTotalHt($invoice->getTjm() * $invoice->getDaysCount());
    }

    public function updatePeriod(Invoice $invoice)
    {
        list ($annualyPeriod, $quarterlyPeriod) = $this->declarationService->getCurrentPeriod();
        $invoice->setPeriod($quarterlyPeriod);
    }

    /**
     * @param \DateTime $currentDate
     * @param Company $company
     * @param array $activities
     * @return Invoice
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function createByActivities(\DateTime $currentDate, Company $company, array $activities): Invoice
    {
        $number = $this->invoiceRepository->getNewInvoiceNumber($currentDate);

        $invoice = new Invoice();
        $invoice->setNumber($number);
        $invoice->setCompany($company);
        $invoice->importActivities($activities);

        $experiences = $this->experienceRepository->findByDate($currentDate);
        if ($experiences && count($experiences) == 1) {
            $invoice->setExperience($experiences[0]);
        }

        $this->calculTva($invoice);

        $this->entityManager->persist($invoice);
        $this->entityManager->flush();

        return $invoice;
    }

    /**
     * Envoi d'un mail si la date d'échéance d'une facture approche et qu'elle n'est toujours pas indiqué comme payé
     * @return array
     * @throws \Exception
     */
    public function getNotifications()
    {
        $date = new \DateTime();
        $notifications = [];

        $unpayedInvoices = $this->invoiceRepository->findInvoicesBy(null, null, false);

        if (count($unpayedInvoices) > 0) {
            foreach ($unpayedInvoices as $invoice) {
                if ($invoice->getDueDate() && $invoice->getDueDate() < $date) {
                    $notifications[] = 'Facture ' .$invoice->getNumber().
                        ' de '.$invoice->getTotalTtc().'€ TTC à encaisser depuis le ' . $invoice->getDueDate()->format('d/m/Y');
                }
            }
        }

        return $notifications;
    }
}