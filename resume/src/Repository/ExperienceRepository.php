<?php

namespace App\Repository;

use App\Entity\Experience;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

/**
 * @method Experience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experience[]    findAll()
 * @method Experience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    /**
     * @return Experience[]
     * @throws Exception
     */
    public function getCurrents(): array
    {
        $query = $this->createQueryBuilder('e');

        $query
            ->orWhere(
                $query->expr()->orX(
                    $query->expr()->isNull('e.dateEnd'),
                    $query->expr()->gte('e.dateEnd', ':date')
                )
            )
            ->setParameter('date', (new DateTime())->sub(new DateInterval('P1M'))->format('Y-m-d'));

        return $query->getQuery()->getResult();
    }

    /**
     * @return Experience[]
     */
    public function findByDate(DateTime $date): array
    {
        $query = $this->createQueryBuilder('e')
            ->setParameter('date', $date->format('Y-m-d'));

        $query->where(':date BETWEEN e.dateBegin AND e.dateEnd')
            ->orWhere(
                $query->expr()->andX(
                    $query->expr()->gte(':date', 'e.dateBegin'),
                    $query->expr()->isNull('e.dateEnd')
                )
            );

        return $query
            ->getQuery()
            ->getResult();
    }
}
