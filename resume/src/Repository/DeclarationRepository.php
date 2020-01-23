<?php

namespace App\Repository;

use App\Entity\Declaration;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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

    public function getByInvoice(string $type, Invoice $invoice)
    {
        return $this->getByDate($type, $invoice->getPayedAtYear(), $invoice->getPayedAtQuarter());
    }

    public function getByDate($type, int $year, int $quarter = 0)
    {
        $query = $this->createQueryBuilder('d')
            ->where('d.type = :type')
            ->join('d.period', 'p')
            ->setParameter('type', $type)
            ->andWhere('p.year = :year')
            ->setParameter('year', $year)
        ;

        if ($type === Declaration::TYPE_SOCIAL) {
            $query->andWhere('p.quarter = :quarter')
                ->setParameter('quarter', $quarter);
        }

        return $query->getQuery()
            ->getOneOrNullResult();
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
