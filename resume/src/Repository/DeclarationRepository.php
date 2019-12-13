<?php

namespace App\Repository;

use App\Entity\Declaration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Declaration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Declaration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Declaration[]    findAll()
 * @method Declaration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeclarationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Declaration::class);
    }

    // /**
    //  * @return Declaration[] Returns an array of Declaration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('d.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Declaration
    {
        return $this->createQueryBuilder('d')
            ->andWhere('d.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
