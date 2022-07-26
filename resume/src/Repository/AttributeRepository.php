<?php

namespace App\Repository;

use App\Entity\Attribute;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\QueryException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Attribute|null find($id, $lockMode = null, $lockVersion = null)
 * @method Attribute|null findOneBy(array $criteria, array $orderBy = null)
 * @method Attribute[]    findAll()
 * @method Attribute[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AttributeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Attribute::class);
    }

    /**
     * @throws QueryException
     */
    public function findAllIndexedBy($attribute, $isListable)
    {
        $query = $this->createQueryBuilder('a');

        $query->where('a.isListable = :isListable')
            ->setParameter('isListable', $isListable);

        return $query->indexBy('a', 'a.' . $attribute)
            ->orderBy('a.weight', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
