<?php

namespace App\Repository;

use App\Entity\Hobby;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Hobby|null find($id, $lockMode = null, $lockVersion = null)
 * @method Hobby|null findOneBy(array $criteria, array $orderBy = null)
 * @method Hobby[]    findAll()
 * @method Hobby[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class HobbyRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hobby::class);
    }
}
