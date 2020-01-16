<?php

namespace App\Repository;

use App\Entity\Experience;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Experience|null find($id, $lockMode = null, $lockVersion = null)
 * @method Experience|null findOneBy(array $criteria, array $orderBy = null)
 * @method Experience[]    findAll()
 * @method Experience[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExperienceRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Experience::class);
    }

    /**
     * @return Experience[]
     */
    public function getCurrents(): array
    {
        $query = $this->createQueryBuilder('e')
            ->where('e.dateEnd IS NULL');

        return $query->getQuery()->getResult();
    }

    /**
     * @param \DateTime $date
     * @return Experience[]
     */
    public function findByDate(\DateTime $date): array
    {
        $query = $this->createQueryBuilder('e')
            ->setParameter('date', $date->format('Y-m-d'));

        $query->where(':date BETWEEN e.dateBegin AND e.dateEnd')
        ->orWhere(
            $query->expr()->andX(
                $query->expr()->gte( ':date', 'e.dateBegin'),
                $query->expr()->isNull('e.dateEnd')
            )
        );

        return $query
            ->getQuery()
            ->getResult();
    }

//    /**
//     * @return Experience[] Returns an array of Experience objects
//     */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('e.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Experience
    {
        return $this->createQueryBuilder('e')
            ->andWhere('e.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
