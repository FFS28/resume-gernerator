<?php

namespace App\Repository;

use App\Entity\Operation;
use App\Enum\OperationTypeEnum;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Operation|null find($id, $lockMode = null, $lockVersion = null)
 * @method Operation|null findOneBy(array $criteria, array $orderBy = null)
 * @method Operation[]    findAll()
 * @method Operation[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class OperationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Operation::class);
    }

    public function listYears(): array
    {
        return $this->createQueryBuilder('o')
            ->select('DISTINCT ToChar(o.date, \'YYYY\') AS date')
            ->getQuery()
            ->getScalarResult();
    }

    /**
     * @throws NonUniqueResultException
     */
    public function findDateNameAmount($date, $name, $amount)
    {
        return $this->createQueryBuilder('o')
            ->andWhere('o.date = :date')
            ->andWhere('o.name = :name')
            ->andWhere('o.amount = :amount')
            ->setParameter('date', $date)
            ->setParameter('name', $name)
            ->setParameter('amount', $amount)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getTotalsByMonthAndType($year = null, $type = null)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.amount) total')
            ->addSelect('o.type')
            ->addSelect('ToChar(o.date, \'YYYY-MM\') AS date')
            ->where('o.type != :hidden')->setParameter('hidden', OperationTypeEnum::Hidden)
            ->orderBy('date', 'asc')
            ->addGroupBy('date')
            ->addGroupBy('o.type');

        if (!$type) {
            $query->andWhere('o.type != :other')->setParameter('other', OperationTypeEnum::Other);
        }

        if ($year) {
            $query->andWhere('ToChar(o.date, \'YYYY\') = :year')->setParameter('year', $year);
        }

        if ($type) {
            $query->andWhere('o.type = :type')->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    public function getTotalsByMonthAndLabel($year = null, $type = null)
    {
        $query = $this->createQueryBuilder('o')
            ->select('SUM(o.amount) total')
            ->addSelect('o.label')
            ->addSelect('ToChar(o.date, \'YYYY-MM\') AS date')
            ->where('o.type != :hidden')->setParameter('hidden', OperationTypeEnum::Hidden)
            ->orderBy('date', 'asc')
            ->addGroupBy('date')
            ->addGroupBy('o.label');

        if ($year) {
            $query->andWhere('ToChar(o.date, \'YYYY\') = :year')->setParameter('year', $year);
        }

        if ($type) {
            $query->andWhere('o.type = :type')->setParameter('type', $type);
        }

        return $query->getQuery()->getResult();
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countNullTypes(): int
    {
        $query = $this->createQueryBuilder('o')
            ->select('COUNT(o.id) nullCount')
            ->where('o.type IS NULL');

        return $query->getQuery()->getSingleScalarResult();
    }
}
