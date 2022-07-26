<?php

namespace App\Repository;

use App\Entity\OperationFilter;
use App\Enum\OperationTypeEnum;
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

    public function getPositiveFilters(): array|float|int|string
    {
        $query = $this->createQueryBuilder('o')
            ->select('o.name')
            ->andWhere('o.amount IS NULL')
            ->andWhere('o.date IS NULL');

        $query->andWhere(
            $query->expr()->in('o.type', [
                OperationTypeEnum::Income->value, OperationTypeEnum::Refund->value
            ])
        );

        return $query->getQuery()->getScalarResult();
    }

    public function getPositiveExceptionFilters()
    {
        $query = $this->createQueryBuilder('o')
            ->select('o.name')
            ->addSelect('o.date')
            ->addSelect('o.amount')
            ->andWhere('o.amount IS NOT NULL')
            ->andWhere('o.date IS NOT NULL');

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
            ->andWhere('o.date IS NULL');

        return $query->getQuery()->getResult();
    }

    public function getLabelsAndTypes(): array
    {
        $query = $this->createQueryBuilder('o')
            ->select('o.type')
            ->addSelect('o.label')
            ->distinct()
            ->andWhere('o.label IS NOT NULL')
            ->andWhere('o.label != :empty')
            ->setParameter('empty', '');
        return $query->getQuery()->getScalarResult();
    }
}
