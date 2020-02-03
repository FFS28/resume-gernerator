<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Purchase;
use App\Repository\PurchaseRepository;
use App\Service\DeclarationService;
use App\Service\PurchaseService;
use Doctrine\ORM\EntityManager;
use http\Client\Request;
use Konekt\PdfPurchase\PurchasePrinter;
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
use thiagoalessio\TesseractOCR\TesseractOCR;

class PurchaseController extends EasyAdminController
{
    /** @var PurchaseRepository */
    private $purchaseRepository;

    /** @var PurchaseService */
    private $purchaseService;

    /** @var TranslatorInterface */
    private $translator;

    public function __construct(PurchaseRepository $purchaseRepository,
                                PurchaseService $purchaseService,
                                TranslatorInterface $translator)
    {
        $this->purchaseRepository = $purchaseRepository;
        $this->purchaseService = $purchaseService;
        $this->translator = $translator;
    }

    public function ocrAction()
    {
        $id = $this->request->query->get('id');
        /** @var Purchase $entity */
        $entity = $this->em->getRepository(Purchase::class)->find($id);

        if ($entity) {
            $filePath = $this->getParameter('PROOF_DIRECTORY').$entity->getProof();
            $tsa = new TesseractOCR($filePath);

            $tsa->executable($this->getParameter('APP_DIR').'/bin/tesseract');

            echo ($tsa)
                ->run();
            exit;

            //$this->em->flush();
        }


        return $this->redirectToReferrer();
    }

    /**
     * @param Purchase $entity
     * @throws \Exception
     */
    protected function persistEntity($entity){
        $this->purchaseService->updatePeriod($entity);

        parent::persistEntity($entity);
    }

    /**
     * @param Purchase $entity
     * @throws \Exception
     */
    protected function updateEntity($entity){
        $this->purchaseService->updatePeriod($entity);

        parent::updateEntity($entity);
    }
}
