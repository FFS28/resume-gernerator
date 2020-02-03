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
