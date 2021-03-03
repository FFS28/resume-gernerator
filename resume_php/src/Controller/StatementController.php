<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Statement;
use App\Repository\StatementRepository;
use App\Service\StatementService;

class StatementController extends EasyAdminController
{
    /**
     * @var StatementRepository
     */
    private $statementRepository;
    /**
     * @var StatementService
     */
    private $statementService;

    public function __construct(StatementRepository $statementRepository,
                                StatementService $statementService)
    {
        $this->statementRepository = $statementRepository;
        $this->statementService = $statementService;
    }

    public function ocrAction()
    {
        $id = $this->request->query->get('id');
        /** @var Statement $entity */
        $entity = $this->statementRepository->find($id);

        if ($entity) {
            $this->statementService->extractOperations($entity);
        }

        return $this->redirectToReferrer();
    }

    /**
     * @param Statement $statement
     */
    protected function persistEntity($statement)
    {
        $statement->setDate(new \DateTime());
        parent::persistEntity($statement);
        $this->postUpdate($statement);
        $this->em->persist($statement);
        $this->em->flush();
    }

    /**
     * @param Statement $statement
     */
    protected function updateEntity($statement)
    {
        parent::updateEntity($statement);
        $this->postUpdate($statement);
        $this->statementService->extractOperations($statement);
    }

    /**
     * @param Statement $statement
     */
    public function postUpdate($statement)
    {
        $matches = [];
        preg_match('#xtrait de comptes Compte \d+ \d+.. C_C EUROCOMPTE DUO CONFORT M ACHAIN JEREMY au (\d{4}-\d{2}-\d{2})#i', $statement->getFilename(), $matches);
        if (count($matches) === 2) {
            $statement->setDate(\DateTime::createFromFormat('Y-m-d', $matches[1]));
        }
    }
}
