<?php

namespace App\Service;

use App\Entity\Declaration;
use App\Entity\Experience;
use App\Entity\Invoice;
use App\Repository\ExperienceRepository;
use App\Repository\DeclarationRepository;
use App\Repository\InvoiceRepository;
use App\Repository\PeriodRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExperienceService
{
    /** @var EntityManagerInterface */
    private $entityManager;

    /** @var ExperienceRepository */
    private $ExperienceRepository;

    public function __construct(
        EntityManagerInterface $entityManager
    ) {
        $this->entityManager = $entityManager;
    }

    public function update(array $ids)
    {
        $repository = $this->entityManager->getRepository(Experience::class);

        foreach ($ids as $id) {
            /** @var Experience $experience */
            $experience = $repository->find($id);

            $search = [];
            if ($experience->getCompany()) {
                $search[] = $experience->getCompany()->getDisplayName();
            }
            if ($experience->getClient()) {
                $search[] = $experience->getClient()->getDisplayName();
            }
            if ($experience->getDateBegin()) {
                $search[] = $experience->getDateBegin()->format('dd/MM/yyyy');
            }
            if ($experience->getDateEnd()) {
                $search[] = $experience->getDateEnd()->format('dd/MM/yyyy');
            }

            $experience->setSearch(implode(' - ', $search));
        }
        $this->entityManager->flush();
    }
}
