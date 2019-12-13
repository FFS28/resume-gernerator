<?php

namespace App\Controller;

use AlterPHP\EasyAdminExtensionBundle\Controller\EasyAdminController;
use App\Entity\Attribute;
use App\Entity\Company;
use App\Entity\Declaration;
use App\Entity\Education;
use App\Entity\Experience;
use App\Entity\Hobby;
use App\Entity\Invoice;
use App\Entity\Link;
use App\Entity\Skill;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AdminController extends EasyAdminController
{
    protected function createListQueryBuilder($entityClass, $sortDirection, $sortField = null, $dqlFilter = null)
    {
        /* @var EntityManager */
        $em = $this->getDoctrine()->getManagerForClass($this->entity['class']);

        /* @var QueryBuilder */
        $queryBuilder = $em->createQueryBuilder()
            ->select('entity')
            ->from($this->entity['class'], 'entity')
        ;

        if (!empty($dqlFilter)) {
            $queryBuilder->andWhere($dqlFilter);
        }

        switch ($entityClass) {
            case Attribute::class:
                $queryBuilder->addOrderBy('entity.weight', 'DESC');
                break;

            case Link::class:
            case Hobby::class:
            case Company::class:
                $queryBuilder->addOrderBy('entity.name', 'ASC');
                break;

            case Education::class:
                $queryBuilder->addOrderBy('entity.level', 'DESC');
                break;

            case Experience::class:
                $queryBuilder->addOrderBy('entity.onHomepage', 'DESC');
                $queryBuilder->addOrderBy('entity.dateBegin', 'DESC');
                break;

            case Invoice::class:
                $queryBuilder->addOrderBy('entity.createdAt', 'DESC');
                break;

            case Skill::class:
                $queryBuilder->addOrderBy('entity.onHomepage', 'DESC');
                $queryBuilder->addOrderBy('entity.name', 'DESC');
                break;

            case Declaration::class:
                $queryBuilder->addOrderBy('entity.year', 'DESC');
                $queryBuilder->addOrderBy('entity.quarter', 'DESC');
                $queryBuilder->addOrderBy('entity.month', 'DESC');
                break;

            default:
                break;
        }

        return $queryBuilder;
    }
}
