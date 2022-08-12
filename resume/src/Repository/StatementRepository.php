<?php

namespace App\Repository;

use App\Entity\Statement;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Statement|null find($id, $lockMode = null, $lockVersion = null)
 * @method Statement|null findOneBy(array $criteria, array $orderBy = null)
 * @method Statement[]    findAll()
 * @method Statement[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StatementRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Statement::class);
    }

    public function listYears(): array
    {
        return $this->createQueryBuilder('s')
            ->select('DISTINCT ToChar(s.date, \'YYYY\') AS date')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countNoOcr(): int
    {
        $query = $this->createQueryBuilder('s')
            ->select('COUNT(s.id) nullCount')
            ->where('s.operationsCount = 0')
            ->orWhere('s.operationsCount IS NULL');

        return $query->getQuery()->getSingleScalarResult();
    }

    public function getSavingAmounts(int $year = null): array|float|int|string
    {
        $query = $this->createQueryBuilder('s')
            ->select('ToChar(s.date, \'YYYY-MM-DD\') AS date')
            ->addSelect('s.savingAmount')
            ->orderBy('date', 'ASC')
        ;

        if ($year) {
            $query->andWhere('ToChar(s.date, \'YYYY\') = :year')->setParameter('year', $year);
        }

        return $query->getQuery()->getArrayResult();
    }
}
