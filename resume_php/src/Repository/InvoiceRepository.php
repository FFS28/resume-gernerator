<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Invoice;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

/**
 * @method Invoice|null find($id, $lockMode = null, $lockVersion = null)
 * @method Invoice|null findOneBy(array $criteria, array $orderBy = null)
 * @method Invoice[]    findAll()
 * @method Invoice[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class InvoiceRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Invoice::class);
    }

    /**
     * @return int[]
     */
    public function findYears(): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('ToChar(i.payedAt, \'YYYY\') AS year')
            ->orderBy('year')
            ->distinct();

        return array_map(function($arr) {return $arr['year'];}, $query->getQuery()->getScalarResult());
    }

    /**
     * @param \DateTime $date
     * @return string
     * @throws NonUniqueResultException
     */
    public function getNewInvoiceNumber($date = null): string
    {
        if (!$date) {
            $date = new \DateTime('now');
        }

        $date_number = $date->format(Invoice::NUMBER_DATE_FORMAT);
        $lastNumber = null;

        $lastInvoice = $this->createQueryBuilder('i')
            ->select('i.number')
            ->where('i.number LIKE :date')
            ->setParameter('date', $date_number . '%')
            ->orderBy('i.number', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if ($lastInvoice && strpos($lastInvoice['number'], '-') > -1) {
            $lastNumber = substr($lastInvoice['number'], strpos($lastInvoice['number'], '-') + 1);
        }

        return $date_number . (intval($lastNumber) + 1);
    }

    /**
     * @param \DateTime $date
     * @return Invoice[]
     */
    public function getByDate(\DateTime $date): array
    {
        return $this->createQueryBuilder('i')
        ->where('ToChar(i.createdAt, \'YYYYMM\') = :date')
        ->setParameter('date', $date->format('Ym'))
        ->getQuery()
        ->getResult();
    }

    /**
     * @param Company $company
     * @param \DateTime $date
     * @return Invoice[]
     */
    public function getByCompanyAndDate(Company $company, \DateTime $date): array
    {
        return $this->createQueryBuilder('i')
        ->where('i.company = :company')
        ->andWhere('ToChar(i.createdAt, \'YYYYMM\') = :date')
        ->setParameter('company', $company)
        ->setParameter('date', $date->format('Ym'))
        ->getQuery()
        ->getResult();
    }

    /**
     * @param QueryBuilder $query
     * @param int $year
     * @param int $quarter
     * @param bool $isPayed
     * @return QueryBuilder
     */
    private function addFilters(QueryBuilder $query, int $year = null, int $quarter = null, $isPayed = null): QueryBuilder
    {
        if ($year !== null) {
            $query->andWhere('ToChar(i.payedAt, \'YYYY\') = :year')
                ->setParameter('year', (string) $year);
        }

        if ($quarter !== null) {
            $query->andWhere('ToChar(i.payedAt, \'Q\') = :quarter')
                ->setParameter('quarter', (string) $quarter);
        }

        if ($isPayed !== null) {
            if ($isPayed == true) {
                $query->andWhere('i.payedAt IS NOT NULL');
            } else {
                $query->andWhere('i.payedAt IS NULL');
            }
        }

        return $query;
    }

    /**
     * @param int $year
     * @param int $quarter
     * @param bool $isPayed
     * @return Invoice[]
     */
    public function findInvoicesBy(int $year = null, int $quarter = null, $isPayed = null): array
    {
        $query = $this->createQueryBuilder('i');

        $query = $this->addFilters($query, $year, $quarter, $isPayed);

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @param string $groupBy
     * @param int $year
     * @param int $quarter
     * @param bool $isPayed
     * @return Invoice[]
     */
    public function getSalesRevenuesGroupBy(string $groupBy, int $year = null, int $quarter = null, $isPayed = true): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalHt) total');

        switch ($groupBy) {
            case 'month':
                $query->addSelect('ToChar(i.payedAt, \'MM\') AS month')
                    ->orderBy('month')
                    ->groupBy('month');
                break;

            case 'quarter':
                $query->addSelect('ToChar(i.payedAt, \'Q\') AS quarter')
                    ->orderBy('quarter')
                    ->groupBy('quarter');
                break;

            case 'year':
            default:
                $query->addSelect('ToChar(i.payedAt, \'YYYY\') AS year')
                    ->orderBy('year')
                    ->groupBy('year');
                break;
        }

        $query = $this->addFilters($query, $year, $quarter, $isPayed);

        return $query->getQuery()->getResult();
    }

    public function getDaysCountByMonth(int $year)
    {
        $query = $this->createQueryBuilder('i')
            ->select('ToChar(i.payedAt, \'MM\') AS month')
            ->addSelect('SUM(i.daysCount) total')
            ->orderBy('month')
            ->groupBy('month');

        $query = $this->addFilters($query, $year, null, null);

        return array_map(function($arr) {$arr['total'] = floatval($arr['total']); return $arr;}, $query->getQuery()->getResult());
    }

    /**
     * @param int $year
     * @return float
     * @throws NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getDaysCountByYear(int $year)
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalHt / i.tjm) total');

        $query = $this->addFilters($query, $year, null, null);

        return floatval($query->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     * @param null $isPayed
     * @return int
     * @throws NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getSalesRevenuesBy(int $year = null, int $quarter = null, $isPayed = true): int
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalHt) total');

        $query = $this->addFilters($query, $year, $quarter, $isPayed);

        return intval($query->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     * @param null $isPayed
     * @return int
     * @throws NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    public function getSalesTaxesBy(int $year = null, int $quarter = null, $isPayed = null): int
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalTax) total');

        $query = $this->addFilters($query, $year, $quarter, $isPayed);

        return intval($query->getQuery()->getSingleScalarResult());
    }

    public function remainingDaysBeforeTaxLimit(): float
    {
        $remainingRevenues = Invoice::LIMIT_AE_TVA - $this->getSalesRevenuesBy((new \DateTime('now'))->format('Y'));

        return $remainingRevenues < 0 ? 0 : $remainingRevenues / Invoice::TJM_DEFAULT;
    }

    public function remainingDaysBeforeLimit(): float
    {
        $remainingRevenues = Invoice::LIMIT_AE - $this->getSalesRevenuesBy((new \DateTime('now'))->format('Y'));

        return $remainingRevenues < 0 ? 0 : $remainingRevenues / Invoice::TJM_DEFAULT;
    }

    public function isOutOfTaxLimit($newInvoiceAmount = 0): bool
    {
        return ($this->getSalesRevenuesBy() + $newInvoiceAmount) >= Invoice::LIMIT_AE_TVA;
    }

    public function isOutOfLimit($newInvoiceAmount = 0): bool
    {
        return ($this->getSalesRevenuesBy() + $newInvoiceAmount) >= Invoice::LIMIT_AE;
    }

    // /**
    //  * @return Invoice[] Returns an array of Invoice objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('i.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Invoice
    {
        return $this->createQueryBuilder('i')
            ->andWhere('i.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
