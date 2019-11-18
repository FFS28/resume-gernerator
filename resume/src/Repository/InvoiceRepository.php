<?php

namespace App\Repository;

use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @param \DateTime $date
     * @return string
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getNewInvoiceNumber($date): string
    {
        if (!$date) {
            $date = new \DateTime('now');
        }

        $date_number = $date->format(Invoice::NUMBER_DATE_FORMAT);

        $lastNumber = $this->createQueryBuilder('i')
            ->select('i.number')
            ->where('i.number LIKE :date')
            ->setParameter('date', $date_number . '%')
            ->orderBy('i.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getSingleScalarResult();

        return $date_number . (intval($lastNumber) + 1);
    }

    /**
     * @return int[]
     */
    public function findYears(): array
    {
        $this->createQueryBuilder('i')
            ->select('DATE_FORMAT(i.createdAt, \'Y\')) AS year')
            ->distinct()
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @param int $year
     * @return int
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSalesRevenuesByYear(int $year, $isPayed = false): int
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalHt) total')
            ->where('DATE_FORMAT(i.createdAt, \'Y\')) = :year')
            ->setParameter('year', (string) $year);

        if ($isPayed) {
            $query->andWhere('i.payedAt IS NOT NULL');
        }

        return intval($query->getQuery()->getSingleScalarResult());
    }

    // /**
    //  * @return Invoice[] Returns an array of Invoice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Invoice
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
