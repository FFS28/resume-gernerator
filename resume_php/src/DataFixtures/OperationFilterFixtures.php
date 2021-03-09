<?php

namespace App\DataFixtures;

use App\Entity\Operation;
use App\Entity\OperationFilter;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class OperationFilterFixtures extends Fixture
{
    private $positivSearches = [
        Operation::TYPE_INCOME => [
            "VIR DE M JEREMY ACHAIN",
            "VIR M JEREMY ACHAI",
            "SALAIRE",
            "AVANCE SUR SALAIRE",
        ],
        Operation::TYPE_REFUND => [
            "F RETRO EUC.CONFORT DUO",
            "VIR MUTUELLES DU SOLEIL LIVR",
            "VIR CPMS",
            "VIR WEMIND / CPMS",
            "VIR CPCAM RHONE",
            "REJ FREELANCE     COMPTE SOLDE",
            "VIR CC ACF URSSAF RHONE ALPE",
            "VIR STE FINANCIERE DU PORTE",
            "VIR DGFIP FINANCES PUBLIQUES",
            "VIR DRFIP GRAND EST ET DEPT - MENSUALISE \d+ M\d+ REMB. EXCD. IMPOT",
            "VIR DRFIP GRAND EST ET DEPT - REMB. EXCD. IMPOT",
            "VIR KEOLIS LYON",
        ]
    ];

    private $positivExceptions = [
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

    private $negativSearches = [
        Operation::TYPE_CHARGE => [
            "ECH PRET CAP\+IN" => ["Crédit Mutuel", "Prêt immobilier"],
            "F COTIS EUC.CONFORT DUO" => ["Crédit Mutuel", "Frais bancaire"],
            "FRAIS PAIE CB" => ["Crédit Mutuel", "Frais bancaire"],
            "SYMFONY" => ["SensioLabs", "Serveur"],
            "GANDI" => ["Gandi", "Serveur"],
            "AWS.AMAZON" => ["Amazon", "Serveur"],
            "DEPANNAGE ENGIE" => ["Engie", "Assurance"],
            "MAE ASSURANCE HABITATION" => ["MAE", "Assurance"],
            "MAE ASSURANCE FAMILLE" => ["MAE", "Assurance"],
            "ENGIE SOCIETE ANONYME" => ["Engie", "Électricité"],
            "ENGIE SA" => ["Engie", "Électricité"],
            "PRLV SEPA ENGIE" => ["Engie", "Électricité"],
            "SDC LE REVARD" => ["Le Revard", "Charges"],
            "BOUYGUES TELECOM" => ["Le Revard", "Internet"],
        ],
        Operation::TYPE_FOOD => [
            "CARREFOUR" => ["Carrefour", "Course"],
            "BAIL DISTRIBUTIO" => ["Bail Distribution", "Course"],
            "MOJON ERIC" => ["Marché Croix Luizet", "Course"],
            "MONOPRIX" => ["Monoprix", "Course"],
            "MONOP" => ["Monoprix", "Course"],
            "LIDL" => ["LIDL", "Course"],
            "BIOCOOP" => ["Biocoop", "Course"],
            "PETER MARKET" => ["Chez Tutu", "Course"],
            "PROXI@" => ["Proxi", "Course"],
            "PROXI " => ["Proxi", "Course"],
            "VIVAL" => ["Vival", "Course"],
            "SM CASINO" => ["Casino", "Course"],
            "LYON - CASINO" => ["Casino", "Course"],
            "SAS BRATEVOL" => ["Bratevol", "Course"],
            "THURINS - MME ET M BERNE" => ["Marché", "Course"],
            "PRIMEURS" => ["Primeurs", "Course"],
            "EPICERIE" => ["Epicerie", "Course"],
            "COLLOMB XAVIER" => ["Boucherie", "Course"],
            "FRUISY" => ["Fruisy", "Course"],
            "ACTR" => ["De L'Autre Côté De La Rue", "Course"],
            "BIO C BON" => ["Bio C Bon", "Course"],
            "SARL MAISON TAC" => ["Maison TACCA", "Course"],
            "LA FERME D EMILE" => ["La ferme d'Emile", "Course"],
            "AMT VIANDES" => ["Boucherie Trolliet", "Course"],
            "RELAY" => ["Relay", "Course"],
            "SA BONNARD" => ["Charcuterie Bonnard", "Course"],

            "SELECTA" => ["Selecta", "Restauration"],
            "MOKAMATIC" => ["Mokomatic", "Restauration"],
            "BOULANG JACQUIER" => ["Boulangerie Jacquier", "Restauration"],
            "FOURNIL SISTERS" => ["Boulangerie", "Restauration"],
            "COFFEE PAIN" => ["Coffee Pain", "Restauration"],
            "BOULANGERIE" => ["Bonlangerie", "Restauration"],
            "LA GAUFRERIE" => ["La gaufrerie du parc", "Restauration"],
            "BRIOCHE DOREE" => ["Brioche Dorée", "Restauration"],
            "TEA TEMPORE" => ["Tea Tempore", "Restauration"],
            "RACONTE MOI LA T" => ["Tea Tempore", "Restauration"],
            "THE WHITE RABBIT" => ["The White Rabbit", "Restauration"],
            "COLUMBUS" => ["COLUMBUS", "Restauration"],
            "THYM-CITRON" => ["Le Tandem", "Restauration"],
            "LYON - BOUCHE" => ["Le Bouche Oreille", "Restauration"],
            "BIG FERNAND" => ["Big Fernand", "Restauration"],
            "GOURMIX" => ["Gourmix", "Restauration"],
            "JUSTEAT" => ["JustEat", "Restauration"],
            "JUST EAT" => ["JustEat", "Restauration"],
            "SUBWAY" => ["Subway", "Restauration"],
            "LA CITAD" => ["La Cita", "Restauration"],
            "LA TRINITE - P ET M" => ["P et M", "Restauration"],
            "DOLCE E AMARO" => ["Dolce Amaro", "Restauration"],
            "TRIMARAN" => ["Trimaran", "Restauration"],
            "IN CUISINE" => ["In Cuisine", "Restauration"],
            "MC DONALDS" => ["Mc Donalds", "Restauration"],
            "LE KALICE" => ["Le Kalice", "Restauration"],
            "FLANIGAN'S" => ["Flanigan's", "Restauration"],
            "B DES FRANGINS" => ["Les Frangins", "Restauration"],
            "LYON - IBIZA" => ["Ibiza", "Restauration"],
            "ABCS RESTAURATIO" => ["ABC Restauration", "Restauration"],
            "SKULL FOOD DRINK" => ["Skull Food Drink", "Restauration"],
            "REGARDS DE BREIZ" => ["Regards de breiz", "Restauration"],
            "TER TER DU WHITE" => ["Le terrier du lapin blanc", "Restauration"],
            "LE BROC BAR" => ["Le broc bar", "Restauration"],
            "DIPLOID" => ["Diploid", "Restauration"],
            "LYON - HANK" => ["Hank", "Restauration"],
            "TARTINE ET GOURM" => ["Tartine et gourmandise", "Restauration"],
            "GRIND CAFE" => ["Grind", "Restauration"],
            "CHEZ MATTEO" => ["Chez Matteo", "Restauration"],
            "ZE PIZZA" => ["Ze pizza", "Restauration"],
            "DELICES DE FLOR" => ["Délices de flor", "Restauration"],
            "CHEZ CECILE" => ["Chez cécile", "Restauration"],
            "SUN BML" => ["Sun BML", "Restauration"],
            "VILLA 128" => ["Villa 128", "Restauration"],
            "MILLE PATES" => ["Mille Pates", "Restauration"],
            "SOUPE AUX CAILLO" => ["La soupe aux cailloux", "Restauration"],
            "P'TITS VENTRES" => ["P'tits ventres", "Restauration"],
            "LE SALE GOSSE" => ["Le sale gosse", "Restauration"],
            "EQUILIBRES" => ["Equilibres café", "Restauration"],
            "FLAM'S" => ["Flam's", "Restauration"],
            "FOODWAY" => ["Foodway", "Restauration"],
            "LES ARPENTEURS" => ["Les arpenteurs", "Restauration"],
            "A CHACUN SA TASS" => ["A chacun sa tasse", "Restauration"],
            "STARBUCKS" => ["Starbucks", "Restauration"],
            "L'ETOURDI" => ["L'étourdi", "Restauration"],
            "MAISON POZZOLI" => ["Maison Pozzoli", "Restauration"],
            "POTES AND BOC" => ["Potes and Boc", "Restauration"],
            "PHU CHI FA" => ["Phu Chi Fa", "Restauration"],
            "BEURRE ZINC" => ["Beurre Zinc", "Restauration"],
            "ECGPAUL" => ["Paul", "Restauration"],
            "L'ESCALE" => ["L'escale", "Restauration"],
            "DELIVEROO" => ["DELIVEROO", "Restauration"],
            "EURL LACA" => ["Boulangerie", "Restauration"],
            "LVC LYON" => ["La Vie Claire", "Restauration"],
            "LVC LYN" => ["La Vie Claire", "Restauration"],
            "LA GOUTTE DE BLE" => ["La goutte de blé", "Restauration"],
            "SOLEIL VIVARAIS" => ["La goutte de blé", "Restauration"],
            "JARDIN DU PRE" => ["Jardin du pré", "Restauration"],
            "HE THYM SEL" => ["Hé thym sel", "Restauration"],
            "LE VIEL AUDON" => ["Le viel audon", "Restauration"],
            "AMORINO" => ["Amorino", "Restauration"],
            "FROMAGER" => ["Fromager", "Restauration"],
            "YABIO" => ["Yabio", "Restauration"],
            "MARCY L ETOIL - LACROIX LAVAL" => ["Buvette", "Restauration"],
            "SUSHI" => ["Sushi", "Restauration"],
            "YOGOLICIOUS" => ["Yogilicious", "Restauration"],
            "BISTROT" => ["Bistrot", "Restauration"],
            "VIEUX GARCONS" => ["Vieux Garçons", "Restauration"],
            "LYON 2EME - COCON" => ["Boulangerie", "Restauration"],
            "LYON 2EME - LEMON B" => ["L'atelier", "Restauration"],
            "FOURNIL" => ["Boulangerie", "Restauration"],
            "KOTOPO" => ["Kotopo", "Restauration"],
            "LA LOGE" => ["La Loge", "Restauration"],
            "PDP 3153LYON BSC" => ["PDP", "Restauration"],
            "LA FOURMILIERE" => ["La fourmilière", "Restauration"],
            "BARNADE" => ["Banadé", "Restauration"],
            "MAISON THEVENON" => ["Boulangerie", "Restauration"],
            "TACOS" => ["Tacos", "Restauration"],
            "HARVEL'S" => ["Harvel's", "Restauration"],
            "JUICE SHOP" => ["Juice Shop", "Restauration"],
            "CONSIAL" => ["Paul", "Restauration"],
            "EXPRESSO" => ["Expresso", "Restauration"],
            "L IMMANENCE" => ["L'immanence", "Restauration"],
            "TOUT PETIT CAFE" => ["Tout petit café", "Restauration"],
            "SHRUBBERY" => ["Le Shrubbery", "Restauration"],
            "CROCK N 'ROLL" => ["Crock n 'roll", "Restauration"],
            "BARABAN" => ["Baraban", "Restauration"],
            "COMPTOIR POULET" => ["Comptoir poulet", "Restauration"],
            "EXKI" => ["Exki", "Restauration"],
            "QUI DIT CREPE" => ["Qui dit crèpes", "Restauration"],
            "CANOPUS" => ["Le Luminarium", "Restauration"],
            "GOCHI" => ["Gochi", "Restauration"],
            "CUISINEHALLES" => ["Cuisine Halles", "Restauration"],
            "MAS AMOR" => ["Mas Amor", "Restauration"],
            "KUMA" => ["Kuma", "Restauration"],
            "BOUILLET" => ["Patisserie", "Restauration"],
            "MEPHYSTO" => ["Metphysto", "Restauration"],
            "TONKA" => ["Tonka", "Restauration"],
            "BREST GOURMAND" => ["Brest Gourmand", "Restauration"],
            "LE MOULIN 1883" => ["Le Moulin 1883", "Restauration"],
            "LA CUISINE" => ["La cuisine", "Restauration"],
            "LOOP S PUB" => ["Loop s pub", "Restauration"],
            "ET C'EST" => ["Et c'est", "Restauration"],
            "GASC" => ["Boulangerie", "Restauration"],
            "MACANUDO" => ["Macanudo", "Restauration"],
            "CUISINELYONNAIS" => ["La Cuisine", "Restauration"],
            "RESTAURANT M" => ["Restaurant M", "Restauration"],
            "KHAN TANDOORI" => ["Khan Tandoori", "Restauration"],
            "MJC MONPLAISIR" => ["MJC Monplaisir", "Restauration"],
            "LES ADRETS - LE KINGSTON" => ["Le Kingston", "Restauration"],
            "BUFFET GARE" => ["Buffet Gare", "Restauration"],
            "LYON - NOZE" => ["Le Noze", "Restauration"],
            "LES ADRETS - LES CIMES" => ["Les cimes", "Restauration"],
            "LES ADRETS - LE SAINTMURY" => ["Le saint mury", "Restauration"],
            "LES ADRETS - LE ROCHER BLANC" => ["Le rocher blanc", "Restauration"],
            "PIZZA" => ["Pizza", "Restauration"],
            "SAVEURS GOURMAND" => ["Saveurs gourmand", "Restauration"],
        ],
        Operation::TYPE_SUPPLY => [
            "AMAZON PAYMENTS" => ["Amazon", "Amazon"],
            "AMAZON EU SARL" => ["Amazon", "Amazon"],
            "LEBONCOIN" => ["LeBonCoin", "LeBonCoin"],
            "FNAC" => ["Fnac", "Fnac"],

            "DARING STARFISH" => ["Point Q", "Sextoy"],
            "MONDIAL CARTOUCH" => ["Mondial Cartouche", "Encre"],
            "BOTANIC" => ["Botanic", "Jardinage"],
            "MONPETITCOINVERT" => ["Mon petit coin vert", "Jardinage"],
            "A D S" => ["ADS", "Bricolage"],
            "LEROY MERLIN" => ["Leroy Merlin", "Bricolage"],
            "QUINCAILLERIE" => ["Quincaillerie", "Bricolage"],
            "LYON LOISIRS" => ["Lyon Loisirs", "Papeterie"],
            "DECATHLON" => ["Décathlon", "Sport"],
            "DECAT 1994" => ["Décathlon", "Sport"],
            "MAISONS DU MONDE" => ["Maison du monde", "Maison"],
            "PUCES DU CANAL" => ["Puces du canal", "Maison"],
            "LITTLE EXTRA" => ["Little Extra", "Maison"],

            "QWERTEE.COM" => ["Qwertee", "Vêtement"],
            "GRAND VOILE" => ["Grand Voile", "Vêtement"],
            "CELIO" => ["Celio", "Vêtement"],
            "TIMBERLAND" => ["Timberland", "Vêtement"],

            "Orbesonge" => ["Orbesonge", "Autre"],
        ],
        Operation::TYPE_SUBSCRIPTION => [
            "AMAZON PRIME FR" => ["Amazon", "Films/Séries"],
            "SPOTIFY" => ["Spotify", "Musiques"],
            "NETFLIX" => ["Netflix", "Films/Séries"],
            "LASTPASS" => ["LastPass", "Informatique"],
            "JETBRAINS" => ["Jetbrains", "Informatique"],
        ],
        Operation::TYPE_HOBBY => [
            "NINTENDO" => ["Nintendo", "Jeu vidéo"],
            "EA \*ORIGIN" => ["EA", "Jeu vidéo"],
            "STEAM PURCHASE" => ["Valve", "Jeu vidéo"],
            "STEAMGAMES.COM" => ["Valve", "Jeu vidéo"],
            "VALVE" => ["Valve", "Jeu vidéo"],

            "GOOGLE" => ["Google", "Application"],

            "LE MONDE EN JEU" => ["Nintendo", "Jeu de société"],
            "TROLLUNE" => ["Nintendo", "Jeu de société"],

            "JM2BD" => ["Experience Bis", "Livre"],
            "LIB PASSAGES" => ["Passages", "Livre"],
            "LETTRES A CROQUE" => ["Lettres à croqué", "Livre"],
            "BDFUGUE" => ["BD Fugue", "Livre"],
            "LIBRAIRIE" => ["Librairie", "Livre"],

            "BOWLING" => ["Bowling", "Loisir"],
            "VELO PROMENADE" => ["Vélo promenade", "Loisir"],
            "FEELING FOREST" => ["Feeling Forest", "Loisir"],
            "SKI BREAK" => ["Ski break", "Loisir"],
            "SNOOKER CHARLEM" => ["Billard Charlemagne", "Loisir"],

            "G SFORZA COIFF" => ["G SFORZA", "Coiffeur"],
            "COIFFURE" => ["Coiffeur", "Coiffeur"],

            "ULULE" => ["Ulule", "Crowdfunding"],
            "PATREON" => ["Patreon", "Crowdfunding"],

            "GAAG FR" => ["Adopte.com", "Rencontre"],

            "BLABLACAR" => ["Blablacar", "Transport"],
            "SNCF" => ["Sncf", "Transport"],
            "BUS ET CLIC" => ["Bus", "Transport"],
            "RHODANIENNE" => ["Bus", "Transport"],
            "S.L.T.C." => ["TCL", "Transport"],
            "VELOV" => ["TCL", "Transport"],

            "UGC" => ["UGC", "Culturel"],
            "CAVERNEDUPONTDAR" => ["Caverne du pont d'ar", "Culturel"],

            "HOTEL" => ["Hotel", "Hotel"],
            "LYONCOH" => ["Novotel", "Hotel"],
            "CAMPANILE" => ["Hotel", "Hotel"],

            " T7L " => ["Téléphérique des Sept Laux", "Ski"],
            "LES ADRETS - 7LO" => ["Skimium - 7LO", "Ski"],
        ],
        Operation::TYPE_OTHER => [
            "PAYPAL" => ["Paypal", "Paypal"],

            "SOFFFA" => ["", "Coworking"],
            "CHEQUE " => ["", "Chèque"],

            "RETRAIT DAB" => ["Banque", "Retrait"],
            "SOCIETE GENERALE" => ["Banque", "Retrait"],
            "RLA AUTOMATE SC" => ["Banque", "Retrait"],

            "URSSAF" => ["URSSAF", "Cotisation"],
            "FINANCES PUBLIQUES" => ["Etat", "Impot"],
            "AMENDE.GOUV" => ["Etat", "Amende"],

            "DR ROBINEAU" => ["Dr Robineau", "Médecine"],
            "DR CUZIN" => ["Dr Cuzin", "Médecine"],
            "BERNEDE AUDREY" => ["Dr Bernede", "Médecine"],
            "PHARMACI" => ["Pharmacie", "Pharmacie"],
            "PHIE FELIX FAUR" => ["Pharmacie", "Pharmacie"],
            "PHARACIE DAVIET" => ["Pharmacie", "Pharmacie"],
            "YLF6918" => ["Pharmacie", "Pharmacie"],

            "ILLUMINATION LYO" => ["Illumination", "Illumination"],
            "SPEED QUEEN" => ["Laverie", "Laverie"],
            "2THELOO GARE" => ["Toilette", "Toilette"],
            "CROISIERES INTER" => ["", "Autre"],
            "ST HILAIRE DE - LEROY ISABELLE" => ["", "Autre"],
            "NOSTRESS" => ["", "Autre"],
            "LA ROCHE SUR - SBM" => ["", "Autre"],
            "DOMPIERRE - IZ *PHU CHI FA A" => ["", "Autre"],
            "LA ROCHE SUR - B GAUDIN" => ["", "Autre"],
            "OFFICE TOURISME" => ["", "Autre"],

            "B6 DEVELOPMENT" => ["???", "???"],
            "HCLFMEDALTYS" => ["???", "???"],
            "CONFLUENCE A." => ["???", "???"],
            "AQUARIUM" => ["???", "???"],
            "SEMACO" => ["???", "???"],
            "ECG15914LPDSTAR2" => ["???", "???"],
            "VIR SEPA 6900116/ 1-346437/ 202" => ["???", "???"],
        ],
        Operation::TYPE_HIDDEN => [
            "VIR LIVRET BLEU" => ["", "Virement"],
            "VIR C/C EUROCOMPTE PRO TRANQUIL" => ["", "Virement"],
            "LYDIA" => ["Lydia", "Virement"],
            "STE FINANCIERE DU PORTE" => ["Lydia", "Virement"],
        ]
    ];

    /**
     * @var ObjectManager
     */
    private $manager;

    public static function getGroups(): array
    {
        return ['filters'];
    }

    public function load(ObjectManager $manager)
    {
        $this->manager = $manager;

        $this->loadOperations();

        $this->manager->flush();
    }

    public function loadOperations()
    {
        foreach ($this->positivSearches as $type => $filters) {
            foreach ($filters as $name) {
                $filter = new OperationFilter();
                $filter->setType($type);
                $filter->setName($name);
                $this->manager->persist($filter);
            }
        }

        foreach ($this->positivExceptions as $exception) {
            $filter = new OperationFilter();
            $filter->setType(Operation::TYPE_REFUND);
            $filter->setName($exception[0]);
            $filter->setDate(\DateTime::createFromFormat('d/m/Y', $exception[1]));
            $filter->setAmount($exception[2]);
            $this->manager->persist($filter);
        }

        foreach ($this->negativSearches as $type => $filters) {
            foreach ($filters as $name => $configuration) {
                $filter = new OperationFilter();
                $filter->setType($type);
                $filter->setName($name);
                $filter->setTarget($configuration[0]);
                $filter->setLabel($configuration[1]);
                $this->manager->persist($filter);
            }
        }
    }
}
