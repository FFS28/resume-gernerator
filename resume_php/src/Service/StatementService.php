<?php

namespace App\Service;


use App\Entity\Activity;
use App\Entity\Operation;
use App\Entity\Statement;
use App\Helper\StringHelper;
use App\Repository\OperationRepository;
use App\Repository\StatementRepository;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Smalot\PdfParser\Parser;
use Spatie\PdfToText\Pdf;
use Symfony\Component\HttpKernel\KernelInterface;

class StatementService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var StatementRepository */
    private $statementRepository;
    /** @var OperationRepository */
    private $operationRepository;
    
    private $statementDirectory;

    public function __construct(
        string $statementDirectory,
        EntityManagerInterface $entityManager,
        StatementRepository $statementRepository,
        OperationRepository $operationRepository
    ) {
        $this->entityManager = $entityManager;
        $this->statementRepository = $statementRepository;
        $this->operationRepository = $operationRepository;
        $this->statementDirectory = $statementDirectory;
    }

    public function extractOperations(Statement $statement)
    {
        $filePath = $this->statementDirectory . $statement->getFilename();
        $operations = [];

        $positivLabels = [
            "VIR DE M JEREMY ACHAIN",
            "VIR M JEREMY ACHAI",
            "F RETRO EUC.CONFORT DUO",
            "VIR MUTUELLES DU SOLEIL LIVR",
            "VIR CPMS",
            "VIR WEMIND / CPMS",
            "VIR CPCAM RHONE",
            "REJ FREELANCE     COMPTE SOLDE",
            "SALAIRE",
            "AVANCE SUR SALAIRE",
            "VIR CC ACF URSSAF RHONE ALPE",
            "VIR STE FINANCIERE DU PORTE",
            "VIR DGFIP FINANCES PUBLIQUES",
            "VIR DRFIP GRAND EST ET DEPT - MENSUALISE \d+ M\d+ REMB. EXCD. IMPOT",
            "VIR DRFIP GRAND EST ET DEPT - REMB. EXCD. IMPOT",
            "VIR KEOLIS LYON",
        ];

        $positivExceptions = [
            ["PAIEMENT CB  0402 PAYLI2441535/ - AMAZON PAYMENTS", '05/02/2021', 3.99],
            ["PAIEMENT CB  0402 PAYLI2441535/ - AMAZON PAYMENTS", '05/02/2021', 15.99],
            ["PAIEMENT CB  1906 PAYLI2441535/ - AMAZON PAYMENTS", '23/06/2020', 16.59],
            ["PAIEMENT CB  2606 PAYLI2441535/ - AMAZON PAYMENTS", '29/06/2020', 16.99],
            ["PAIEMENT CB  0405 PAYLI2441535/ - AMAZON PAYMENTS", '05/05/2020', 10.5],
            ["PAIEMENT CB  1105 PAYLI2441535/ - AMAZON PAYMENTS", '12/05/2020', 12.88],
            ["PAIEMENT CB  0405 ARCHAMPS - BOTANIC", '05/05/2020', 7.55],
            ["PAIEMENT CB  0302 PARIS - LEBONCOIN", '05/02/2021', 11.49],
            ["PAIEMENT CB  1412 PARIS CEDEX 0 - SNCF INTERNET", '15/12/2017', 79.8],
            ["PAIEMENT CB  2810 PARIS - MGP*ULULE SAS", '30/10/2020', 40],
            ["PAIEMENT CB  2905 0800 942 890 - PAYPAL", '01/06/2020', 8.82],
            ["PAIEMENT CB  0812 0800 942 890 - PAYPAL", '09/12/2019', 29.41],
        ];

        // Impossible de diférencier les débits et crédits
        $PDFParser = new Parser();
        $pdf = $PDFParser->parseFile($filePath);
        $text = $pdf->getText();

        $lines = explode("\n", $text);
        $startAmount = $endAmount = $totalAmount = $nbOperations = 0;

        $extractDate = $extractOperations = false;

        foreach ($lines as $index => $line) {
            if ($extractOperations && $index > $extractOperations) {
                if (strpos($line, "Date\tDate valeur\tOpération\tDébit EUROS\tCrédit EUROS\t") === 0) {
                    $extractOperations = false;
                } else {
                    $operation = explode("\t", $line);
                    if (preg_match('#\d{2}/\d{2}/\d{4}#', $operation[0])) {
                        $date = \DateTime::createFromFormat('d/m/Y', $operation[0]);

                        if (!$statement->getDate()) {
                            $statement->setDate($date);
                        }

                        $operations[] = [
                            $date,
                            $operation[2],
                            StringHelper::extractAmount($operation[3])
                        ];
                    } else {
                        $operations[count($operations) - 1][1] .= ' - ' . $operation[0];
                    }
                }
            } elseif (strpos($line, 'Compte Courant JEUNE ACTIF N°') === 0
                || strpos($line, 'C/C EUROCOMPTE DUO CONFORT N°') === 0) {
                $extractOperations = $index + 1;
            } elseif (strpos($line, 'SOLDE CREDITEUR ') > -1) {
                $lineArray = explode("\t", $line);
                $amount = StringHelper::extractAmount(strpos($line, 'SOLDE CREDITEUR ')  === 0 ? $lineArray[1] : $lineArray[2]);

                if ($startAmount && !$endAmount) {
                    $endAmount = $amount;
                } elseif (!$startAmount) {
                    $startAmount = $amount;
                }
            }
        }

        $statement->setOperationsCount(count($operations));

        foreach ($operations as $operationLine) {
            /** @var \DateTime $date */
            $date = $operationLine[0];
            $name = $operationLine[1];
            $amount = $operationLine[2];
            $label = '';
            $isPositiv = false;

            if  (StringHelper::contains($name, $positivLabels) === true) {
                $isPositiv = true;
            }
            foreach ($positivExceptions as $exception) {
                if (strpos($name, $exception[0]) > -1 && $date->format('d/m/Y') === $exception[1] && $amount == $exception[2]) {
                    $isPositiv = true;
                }
            }

            $amount = $isPositiv ? $amount : -$amount;
            $totalAmount += $amount;

            if ($amount > 0) {
                dump($name . ' : ' . $amount);
            }

            if (!$this->operationRepository->findDateNameAmount($date, $name, $amount)) {
                $operation = new Operation();
                $operation->setDate($date);
                $operation->setName($name);
                $operation->setAmount($amount);

                $this->analyseOperation($operation);

                $this->entityManager->persist($operation);
                $nbOperations++;
            }
        }

        if (round($startAmount + $totalAmount, 2) != round($endAmount, 2)) {
            dump($filePath);
            dump("Start", $startAmount, "End : ", $endAmount, "Total : ", $startAmount + $totalAmount);
            foreach ($operations as $operationLine) {
                dump($operationLine);
            }
            throw new \Exception('Les comptes ne tombent pas juste');
        }

        if ($nbOperations === 0) {
            throw new \Exception('Aucune ligne ajouté');
        }

        $this->entityManager->flush();
    }

    public function analyseOperation(Operation $operation)
    {
        $searches = [
            Operation::TYPE_CHARGE => [
                "AWS.AMAZON"                    => ["Amazon", "Sauvegarge NAS"],
                "ECH PRET CAP\+IN"              => ["Crédit Mutuel", "Prêt immobilier"],
                "F COTIS EUC.CONFORT DUO"       => ["Crédit Mutuel", "Frais bancaire"],
                "SYMFONY"                  => ["SensioLabs", "Serveur"],
                "GANDI"                         => ["Gandi", "Serveur"],
                "DEPANNAGE ENGIE"               => ["Engie", "Assurance Dépannage"],
                "ENGIE SOCIETE ANONYME"         => ["Engie", "Électricité"],
                "ENGIE SA"                      => ["Engie", "Électricité"],
                "MAE ASSURANCE HABITATION"      => ["MAE", "Assurance Habitation"],
                "MAE ASSURANCE FAMILLE"         => ["MAE", "Assurance Personnel"],
                "SDC LE REVARD"                 => ["Le Revard", "Charges appartement"],
                "BOUYGUES TELECOM"              => ["Le Revard", "Forfait internet/téléphone"],
                "S.L.T.C."                      => ["TCL", "Transports"],
                "VELOV"                         => ["TCL", "Transports"],
                "FRAIS PAIE CB"                 => ["Crédit Mutuel", "Frais CB"],
                "JETBRAINS"                     => ["Jetbrains", "Logiciel"],
            ],
            Operation::TYPE_FOOD => [
                "CARREFOUR"                     => ["Carrefour", "Course"],
                "BAIL DISTRIBUTIO"              => ["Bail Distribution", "Course"],
                "MOJON ERIC"                    => ["Marché Croix Luizet", "Course"],
                "MONOPRIX"                      => ["Monoprix", "Course"],
                "MONOP"                         => ["Monoprix", "Course"],
                "LIDL"                          => ["LIDL", "Course"],
                "BIOCOOP"                       => ["Biocoop", "Course"],
                "PETER MARKET"                  => ["Chez Tutu", "Course"],
                "PROXI@"                        => ["Proxi", "Course"],
                "PROXI "                        => ["Proxi", "Course"],
                "VIVAL"                         => ["Vival", "Course"],
                "SM CASINO"                     => ["Casino", "Course"],
                "LYON - CASINO"                 => ["Casino", "Course"],
                "SAS BRATEVOL"                  => ["Bratevol", "Course"],
                "THURINS - MME ET M BERNE"      => ["Marché", "Course"],
                "PRIMEURS"                      => ["Primeurs", "Course"],
                "EPICERIE"                      => ["Epicerie", "Course"],
                "COLLOMB XAVIER"                => ["Boucherie", "Course"],
                "FRUISY"                        => ["Fruisy", "Course"],
                "ACTR"                          => ["De L'Autre Côté De La Rue", "Course"],
                "SELECTA"                       => ["Selecta", "Restauration"],
                "MOKAMATIC"                     => ["Mokomatic", "Restauration"],
                "BOULANG JACQUIER"              => ["Boulangerie Jacquier", "Restauration"],
                "FOURNIL SISTERS"               => ["Boulangerie", "Restauration"],
                "COFFEE PAIN"                   => ["Coffee Pain", "Restauration"],
                "BOULANGERIE"                   => ["Bonlangerie", "Restauration"],
                "LA GAUFRERIE"                  => ["La gaufrerie du parc", "Restauration"],
                "LA BRIOCHE DOREE"              => ["Brioche Dorée", "Restauration"],
                "TEA TEMPORE"                   => ["Tea Tempore", "Restauration"],
                "RACONTE MOI LA T"              => ["Tea Tempore", "Restauration"],
                "THE WHITE RABBIT"              => ["The White Rabbit", "Restauration"],
                "COLUMBUS"                      => ["COLUMBUS", "Restauration"],
                "THYM-CITRON"                   => ["Le Tandem", "Restauration"],
                "LYON - BOUCHE"                 => ["Le Bouche Oreille", "Restauration"],
                "BIG FERNAND"                   => ["Big Fernand", "Restauration"],
                "GOURMIX"                       => ["Gourmix", "Restauration"],
                "JUSTEAT"                       => ["JustEat", "Restauration"],
                "JUST EAT"                      => ["JustEat", "Restauration"],
                "SUBWAY"                        => ["Subway", "Restauration"],
                "LA CITAD"                      => ["La Cita", "Restauration"],
                "LA TRINITE - P ET M"           => ["P et M", "Restauration"],
                "DOLCE E AMARO"                 => ["Dolce Amaro", "Restauration"],
                "TRIMARAN"                      => ["Trimaran", "Restauration"],
                "IN CUISINE"                    => ["In Cuisine", "Restauration"],
                "MC DONALDS"                    => ["Mc Donalds", "Restauration"],
                "LE KALICE"                     => ["Le Kalice", "Restauration"],
                "FLANIGAN'S"                    => ["Flanigan's", "Restauration"],
                "B DES FRANGINS"                => ["Les Frangins", "Restauration"],
                "LYON - IBIZA"                  => ["Ibiza", "Restauration"],
                "ABCS RESTAURATIO"              => ["ABC Restauration", "Restauration"],
                "SKULL FOOD DRINK"              => ["Skull Food Drink", "Restauration"],
                "REGARDS DE BREIZ"              => ["Regards de breiz", "Restauration"],
                "TER TER DU WHITE"              => ["Le terrier du lapin blanc", "Restauration"],
                "LE BROC BAR"                   => ["Le broc bar", "Restauration"],
                "DIPLOID"                       => ["Diploid", "Restauration"],
                "LYON - HANK"                   => ["Hank", "Restauration"],
                "TARTINE ET GOURM"              => ["Tartine et gourmandise", "Restauration"],
                "GRIND CAFE"                    => ["Grind", "Restauration"],
                "CHEZ MATTEO"                   => ["Chez Matteo", "Restauration"],
                "ZE PIZZA"                      => ["Ze pizza", "Restauration"],
                "DELICES DE FLOR"               => ["Délices de flor", "Restauration"],
                "CHEZ CECILE"                   => ["Chez cécile", "Restauration"],
                "SUN BML"                       => ["Sun BML", "Restauration"],
                "VILLA 128"                     => ["Villa 128", "Restauration"],
                "MILLE PATES"                   => ["Mille Pates", "Restauration"],
                "SOUPE AUX CAILLO"              => ["La soupe aux cailloux", "Restauration"],
                "P'TITS VENTRES"                => ["P'tits ventres", "Restauration"],
                "LE SALE GOSSE"                 => ["Le sale gosse", "Restauration"],
                "EQUILIBRES"                    => ["Equilibres café", "Restauration"],
                "FLAM'S"                        => ["Flam's", "Restauration"],
                "FOODWAY"                       => ["Foodway", "Restauration"],
                "LES ARPENTEURS"                => ["Les arpenteurs", "Restauration"],
                "A CHACUN SA TASS"              => ["A chacun sa tasse", "Restauration"],
                "STARBUCKS"                     => ["Starbucks", "Restauration"],
                "L'ETOURDI"                     => ["L'étourdi", "Restauration"],
                "MAISON POZZOLI"                => ["Maison Pozzoli", "Restauration"],
                "POTES AND BOC"                 => ["Potes and Boc", "Restauration"],
                "PHU CHI FA"                    => ["Phu Chi Fa", "Restauration"],
                "BEURRE ZINC"                   => ["Beurre Zinc", "Restauration"],
                "ECGPAUL"                       => ["Paul", "Restauration"],
                "L'ESCALE"                      => ["L'escale", "Restauration"],
                "DELIVEROO"                     => ["DELIVEROO", "Restauration"],
                "EURL LACA"                     => ["Boulangerie", "Restauration"],
                "LVC LYON"                      => ["La Vie Claire", "Restauration"],
                "LVC LYN"                       => ["La Vie Claire", "Restauration"],
                "LA GOUTTE DE BLE"              => ["La goutte de blé", "Restauration"],
                "SOLEIL VIVARAIS"               => ["La goutte de blé", "Restauration"],
                "JARDIN DU PRE"                 => ["Jardin du pré", "Restauration"],
                "HE THYM SEL"                   => ["Hé thym sel", "Restauration"],
                "LE VIEL AUDON"                 => ["Le viel audon", "Restauration"],
                "AMORINO"                       => ["Amorino", "Restauration"],
                "FROMAGER"                      => ["Fromager", "Restauration"],
                "YABIO"                         => ["Yabio", "Restauration"],
                "MARCY L ETOIL - LACROIX LAVAL" => ["Buvette", "Restauration"],
                "SUSHI"                         => ["Sushi", "Restauration"],
                "YOGOLICIOUS"                   => ["Yogilicious", "Restauration"],
                "BISTROT"                       => ["Bistrot", "Restauration"],
                "VIEUX GARCONS"                 => ["Vieux Garçons", "Restauration"],
                "LYON 2EME - COCON"             => ["Boulangerie", "Restauration"],
                "LYON 2EME - LEMON B"           => ["L'atelier", "Restauration"],
                "FOURNIL"                       => ["Boulangerie", "Restauration"],
                "KOTOPO"                        => ["Kotopo", "Restauration"],
                "LA LOGE"                       => ["La Loge", "Restauration"],
                "PDP 3153LYON BSC"              => ["PDP", "Restauration"],
                "LA FOURMILIERE"                => ["La fourmilière", "Restauration"],
                "BARNADE"                       => ["Banadé", "Restauration"],
                "MAISON THEVENON"               => ["Boulangerie", "Restauration"],
                "TACOS"                         => ["Tacos", "Restauration"],
                "HARVEL'S"                      => ["Harvel's", "Restauration"],
                "JUICE SHOP"                    => ["Juice Shop", "Restauration"],
                "CONSIAL"                       => ["Paul", "Restauration"],
                "EXPRESSO"                      => ["Expresso", "Restauration"],
                "L IMMANENCE"                   => ["L'immanence", "Restauration"],
                "TOUT PETIT CAFE"               => ["Tout petit café", "Restauration"],
                "SHRUBBERY"                     => ["Le Shrubbery", "Restauration"],
                "CROCK N 'ROLL"                 => ["Crock n 'roll", "Restauration"],
                "BARABAN"                       => ["Baraban", "Restauration"],
                "COMPTOIR POULET"               => ["Comptoir poulet", "Restauration"],
                "EXKI"                          => ["Exki", "Restauration"],
                "QUI DIT CREPE"                 => ["Qui dit crèpes", "Restauration"],
                "CANOPUS"                       => ["Le Luminarium", "Restauration"],
                "GOCHI"                         => ["Gochi", "Restauration"],
                "CUISINEHALLES"                 => ["Cuisine Halles", "Restauration"],
                "MAS AMOR"                      => ["Mas Amor", "Restauration"],
                "KUMA"                          => ["Kuma", "Restauration"],
                "BOUILLET HENRI"                => ["Patisserie", "Restauration"],
                "MEPHYSTO"                      => ["Metphysto", "Restauration"],
                "TONKA"                         => ["Tonka", "Restauration"],
                "BREST GOURMAND"                => ["Brest Gourmand", "Restauration"],
                "LE MOULIN 1883"                => ["Le Moulin 1883", "Restauration"],
                "LA CUISINE"                    => ["La cuisine", "Restauration"],
                "LOOP S PUB"                    => ["Loop s pub", "Restauration"],
                "SUMUP *ET C'EST"               => ["Et c'est", "Restauration"],
                "GASC"                          => ["Boulangerie", "Restauration"],
                "MACANUDO"                      => ["Macanudo", "Restauration"],
            ],
            Operation::TYPE_SUPPLY => [
                "AMAZON PAYMENTS"               => ["Amazon", "Achat"],
                "AMAZON EU SARL"                => ["Amazon", "Achat"],
                "LEBONCOIN"                     => ["LeBonCoin", "Achat"],
                "FNAC"                          => ["Fnac", "Achat"],
                "DARING STARFISH"               => ["Point Q", "Achat"],
                "BOTANIC"                       => ["Botanic", "Achat"],
                "A D S"                         => ["ADS", "Achat"],
                "Orbesonge"                     => ["Orbesonge", "Achat"],
                "LYON LOISIRS"                  => ["Lyon Loisirs", "Achat"],
                "DECATHLON"                     => ["Décathlon", "Achat"],
                "DECAT 1994"                    => ["Décathlon", "Achat"],
                "MAISONS DU MONDE"              => ["Maison du monde", "Achat"],
                "PUCES DU CANAL"                => ["Puces du canal", "Achat"],
                "LITTLE EXTRA"                  => ["Little Extra", "Achat"],
                "MONDIAL CARTOUCH"              => ["Mondial Cartouche", "Achat"],
                "LEROY MERLIN"                  => ["Leroy Merlin", "Achat"],
                "MONPETITCOINVERT"              => ["Mon petit coin vert", "Achat"],
                "QUINCAILLERIE"                 => ["Quincaillerie", "Achat"],
                "SA BONNARD"                    => ["Bonnard", "Achat"],

                "QWERTEE.COM"                   => ["Qwertee", "Vêtement"],
                "GRAND VOILE"                   => ["Grand Voile", "Vêtement"],
                "CELIO"                         => ["Celio", "Vêtement"],
                "TIMBERLAND"                    => ["Timberland", "Vêtement"],
            ],
            Operation::TYPE_SUBSCRIPTION => [
                "AMAZON PRIME FR"               => ["Amazon", "Amazon Prime"],
                "LASTPASS"                      => ["LastPass", "LastPass"],
            ],
            Operation::TYPE_HOBBY => [
                "NINTENDO"                      => ["Nintendo", "Jeu vidéo"],
                "EA *ORIGIN"                    => ["EA", "Jeu vidéo"],
                "STEAM PURCHASE"                => ["Valve", "Jeu vidéo"],
                "STEAMGAMES.COM"                => ["Valve", "Jeu vidéo"],
                "VALVE"                         => ["Valve", "Jeu vidéo"],
                "GOOGLE"                        => ["Google", "Application"],

                "LE MONDE EN JEU"               => ["Nintendo", "Jeu de société"],
                "JM2BD"                         => ["Experience Bis", "Librairie"],
                "LIB PASSAGES"                  => ["Passages", "Librairie"],
                "LETTRES A CROQUE"              => ["Lettres à croqué", "Librairie"],
                "BDFUGUE"                       => ["BD Fugue", "Librairie"],
                "LIBRAIRIE"                     => ["Librairie", "Librairie"],

                "G SFORZA COIFF"                => ["G SFORZA", "Coiffeur"],
                "COIFFURE"                      => ["Coiffeur", "Coiffeur"],
                "BOWLING"                       => ["Bowling", "Loisir"],
                "VELO PROMENADE"                => ["Vélo promenade", "Loisir"],
                "FEELING FOREST"                => ["Feeling Forest", "Loisir"],
                "SKI BREAK"                     => ["Ski break", "Loisir"],
                "SNOOKER CHARLEM"               => ["Billard Charlemagne", "Loisir"],
                "ULULE"                         => ["Ulule", "Crowdfunding"],
                "GAAG FR"                       => ["Adopte.com", "Crowdfunding"],
                "HOTEL"                         => ["Hotel", "Hotel"],
                "CAMPANILE"                     => ["Hotel", "Hotel"],
                "BLABLACAR"                     => ["Blablacar", "Amende"],
                "SNCF"                          => ["Sncf", "Train"],
                "RHODANIENNE"                   => ["Bus Aubanas", "Train"],
                "CAVERNEDUPONTDAR"              => ["Culturel", "Train"],
                "UGC"                           => ["UGC", "Cinéma"],
            ],
            Operation::TYPE_OTHER => [
                "PAYPAL"                        => ["Paypal", ""],
                "ILLUMINATION LYO"              => ["Illumination", ""],
                "SPEED QUEEN"                   => ["Laverie", ""],
                "2THELOO GARE"                  => ["Toilette", ""],
                "CROISIERES INTER"              => ["Autre", ""],
                "ST HILAIRE DE - LEROY ISABELLE"=> ["Autre", ""],
                "NOSTRESS"                      => ["Autre", ""],
                "LA ROCHE SUR - SBM"            => ["Autre", ""],
                "DOMPIERRE - IZ *PHU CHI FA A"  => ["Autre", ""],
                "LA ROCHE SUR - B GAUDIN"       => ["Autre", ""],
                "SOFFFA"                        => ["Coworking", ""],
                "OFFICE TOURISME"               => ["Autre", ""],
                "CHEQUE "                       => ["Chèque", ""],
                "STE FINANCIERE DU PORTE"       => ["Lydia", "Amende"],
                "RETRAIT DAB"                   => ["Banque", "Retrait"],
                "SOCIETE GENERALE"              => ["Banque", "Retrait"],
                "RLA AUTOMATE SC"               => ["Banque", "Retrait"],
                "AMENDE.GOUV"                   => ["Etat", "Amende"],
                "DR ROBINEAU"                   => ["Dr Robineau", "Médecine"],
                "DR CUZIN"                      => ["Dr Cuzin", "Médecine"],
                "BERNEDE AUDREY"                => ["Dr Bernede", "Médecine"],
                "PHARMACI"                      => ["Pharmacie", "Pharmacie"],
                "PHIE FELIX FAUR"               => ["Pharmacie", "Pharmacie"],
                "PHARACIE DAVIET"               => ["Pharmacie", "Pharmacie"],
                "YLF6918"                       => ["Pharmacie", "Pharmacie"],
                "B6 DEVELOPMENT"                => ["???", ""],
                "HCLFMEDALTYS"                  => ["???", ""],
                "CONFLUENCE A."                 => ["???", ""],
                "SEMACO"                        => ["???", ""],
            ],
            Operation::TYPE_INCOME => [

            ],
            Operation::TYPE_REFUND => [

            ],
        ];

        if (!$operation->getLabel()) {
            $operation->setLabel(trim(str_replace('CARTE 12946058', '', $operation->getName())));
        }

        foreach ($searches as $indexType => $searchesType) {
            foreach ($searchesType as $searchOperation => $configurationOperation) {

                if (preg_match('#'.$searchOperation.'#i', $operation->getName(), $matches)) {
                    $operation->setType($indexType);
                    $operation->setTarget($configurationOperation[0]);
                    $operation->setLabel($configurationOperation[1]);
                }
            }
        }

    }
}
