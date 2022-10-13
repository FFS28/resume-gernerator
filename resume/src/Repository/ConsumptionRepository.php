<?php

namespace App\Repository;

use App\Entity\Consumption;
use App\Helper\StringHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Consumption>
 *
 * @method Consumption|null find($id, $lockMode = null, $lockVersion = null)
 * @method Consumption|null findOneBy(array $criteria, array $orderBy = null)
 * @method Consumption[]    findAll()
 * @method Consumption[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumptionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Consumption::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(\DateTime $date): Consumption | null {
        return $this->createQueryBuilder('c')
            ->where('ToChar(c.date, \'YYYYMMDD\') = :date')
            ->setParameter('date', $date->format('Ymd'))
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @param int $year
     * @param int $month
     * @return Consumption[]
     */
    public function listByYearAndMonth(int $year, int $month): array
    {
        return $this->createQueryBuilder('c')
            ->where('ToChar(c.date, \'YYYY\') = :year')
            ->andWhere('ToChar(c.date, \'MM\') = :month')
            ->setParameter('year', $year)
            ->setParameter('month',  StringHelper::addZeros($month, 2))
            ->getQuery()
            ->getResult();
    }

    public function listYears(): array
    {
        return $this->createQueryBuilder('c')
            ->select('DISTINCT ToChar(c.date, \'YYYY\') AS date')
            ->getQuery()
            ->getScalarResult();
    }

    public function add(Consumption $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Consumption $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Consumption[] Returns an array of Consumption objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Consumption
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
