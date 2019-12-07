<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\Company;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Activity|null find($id, $lockMode = null, $lockVersion = null)
 * @method Activity|null findOneBy(array $criteria, array $orderBy = null)
 * @method Activity[]    findAll()
 * @method Activity[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ActivityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Activity::class);
    }

    /**
     * @param \DateTimeInterface $date
     * @return Activity[]
     */
    public function findByDate(\DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('a')
            ->where('ToChar(a.date, \'YYYYMM\') = :date')
            ->andWhere('a.value > 0')
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param Company $company
     * @param \DateTimeInterface $date
     * @return Activity[]
     */
    public function findByCompanyAndDate(Company $company, \DateTimeInterface $date): ?array
    {
        return $this->createQueryBuilder('a')
            ->where('a.company = :company')
            ->andWhere('ToChar(a.date, \'YYYYMM\') = :date')
            ->andWhere('a.value > 0')
            ->setParameter('company', $company)
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @param Invoice $invoice
     * @param \DateTimeInterface $date
     */
    public function cleanByDate(\DateTimeInterface $date)
    {
        $activities = $this->createQueryBuilder('a')
            ->delete()
            ->andWhere('ToChar(a.date, \'YYYYMM\') = :date')
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->execute()
            ;
    }

    // /**
    //  * @return Activity[] Returns an array of Activity objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Activity
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
