<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Operation;
use App\Entity\Statement;
use App\Repository\OperationRepository;
use App\Repository\StatementRepository;
use App\Service\StatementService;

class OperationController extends EasyAdminController
{
    /**
     * @var OperationRepository
     */
    private $operationRepository;
    /**
     * @var StatementService
     */
    private $statementService;

    public function __construct(OperationRepository $operationRepository,
                                StatementService $statementService)
    {
        $this->operationRepository = $operationRepository;
        $this->statementService = $statementService;
    }

    public function analyzeBatchAction()
    {
        $form = $this->request->request->get('batch_form');
        $ids = explode(',', $form['ids']);

        foreach ($ids as $id) {
            /** @var Operation $operation */
            $operation = $this->operationRepository->find($id);

            if ($operation) {
                $this->statementService->analyseOperation($operation);
            }
        }

        $this->em->flush();

        return $this->redirectToReferrer();
    }
}
