<?php

namespace App\Repository;

use App\Entity\ConsumptionMonth;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsumptionMonth>
 *
 * @method ConsumptionMonth|null find($id, $lockMode = null, $lockVersion = null)
 * @method ConsumptionMonth|null findOneBy(array $criteria, array $orderBy = null)
 * @method ConsumptionMonth[]    findAll()
 * @method ConsumptionMonth[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ConsumptionMonthRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsumptionMonth::class);
    }

    /**
     * @throws NonUniqueResultException
     */
    public function get(int $year, int $month): ConsumptionMonth | null {
        return $this->createQueryBuilder('c')
            ->where('c.year = :year')
            ->andWhere('c.month = :month')
            ->setParameter('year', $year)
            ->setParameter('month', $month)
            ->getQuery()
            ->setHydrationMode(AbstractQuery::HYDRATE_OBJECT)
            ->getOneOrNullResult();
    }

    public function add(ConsumptionMonth $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(ConsumptionMonth $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return ConsumptionMonth[] Returns an array of ConsumptionMonth objects
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

//    public function findOneBySomeField($value): ?ConsumptionMonth
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
