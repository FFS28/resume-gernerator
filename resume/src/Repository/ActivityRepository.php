<?php

namespace App\Repository;

use App\Entity\Activity;
use App\Entity\Company;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

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
     * @return Activity[]
     */
    public function findByDate(DateTimeInterface $date): array
    {
        return $this->createQueryBuilder('a')
            ->where('ToChar(a.date, \'YYYYMM\') = :date')
            ->andWhere('a.value > 0')
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return array<Activity>|null
     */
    public function findByCompanyAndDate(Company $company, DateTimeInterface $date): ?array
    {
        return $this->createQueryBuilder('a')
            ->where('a.company = :company')
            ->andWhere('ToChar(a.date, \'YYYYMM\') = :date')
            ->andWhere('a.value > 0')
            ->setParameter('company', $company)
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->getResult();
    }

    public function cleanByDate(DateTimeInterface $date): void
    {
        $this->createQueryBuilder('a')
            ->delete()
            ->andWhere('ToChar(a.date, \'YYYYMM\') = :date')
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->execute();
    }
}
