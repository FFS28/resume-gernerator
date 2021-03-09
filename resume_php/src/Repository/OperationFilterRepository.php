<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Entity\OperationFilter;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method OperationFilter|null find($id, $lockMode = null, $lockVersion = null)
 * @method OperationFilter|null findOneBy(array $criteria, array $orderBy = null)
 * @method OperationFilter[]    findAll()
 * @method OperationFilter[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationFilterRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OperationFilter::class);
    }

    public function getPositiveFilters()
    {
        $query = $this->createQueryBuilder('o')
            ->select('o.name')
            ->andWhere('o.amount IS NULL')
            ->andWhere('o.date IS NULL')
        ;

        $query->andWhere($query->expr()->in('o.type', [
            Operation::TYPE_INCOME, Operation::TYPE_REFUND
        ]));

        return $query->getQuery()->getScalarResult();
    }

    public function getPositiveExceptionFilters()
    {
        $query = $this->createQueryBuilder('o')
            ->select('o.name')
            ->addSelect('o.date')
            ->addSelect('o.amount')
            ->andWhere('o.amount IS NOT NULL')
            ->andWhere('o.date IS NOT NULL')
        ;

        return $query->getQuery()->getResult();
    }

    public function getFilters()
    {
        $query = $this->createQueryBuilder('o')
            ->select('o.type')
            ->addSelect('o.name')
            ->addSelect('o.target')
            ->addSelect('o.label')
            ->andWhere('o.amount IS NULL')
            ->andWhere('o.date IS NULL')
        ;

        return $query->getQuery()->getResult();
    }

    // /**
    //  * @return OperationFilter[] Returns an array of OperationFilter objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('o.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?OperationFilter
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
