<?php

namespace App\Repository;

use App\Entity\Company;
use App\Entity\Invoice;
use App\Enum\InvoiceStatusEnum;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

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
            ->where('i.payedAt IS NOT NULL')
            ->distinct();

        return array_map(fn($arr) => $arr['year'], $query->getQuery()->getScalarResult());
    }

    /**
     * @param DateTime|null $date
     * @throws NonUniqueResultException
     */
    public function getNewInvoiceNumber(DateTime $date = null): string
    {
        if (!$date) {
            $date = new DateTime('now');
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

        if ($lastInvoice && strpos((string)$lastInvoice['number'], '-') > -1) {
            $lastNumber = substr((string)$lastInvoice['number'], strpos((string)$lastInvoice['number'], '-') + 1);
        }

        return $date_number . (intval($lastNumber) + 1);
    }

    /**
     * @return Invoice[]
     */
    public function getByDate(DateTime $date): array
    {
        return $this->createQueryBuilder('i')
            ->where('ToChar(i.createdAt, \'YYYYMM\') = :date')
            ->setParameter('date', $date->format('Ym'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @return Invoice[]
     */
    public function getByCompanyAndDate(Company $company, DateTime $date): array
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
     * @param int|null $year
     * @param int|null $quarter
     * @param null $isPayed
     * @return array<Invoice>
     */
    public function findInvoicesBy(int $year = null, int $quarter = null, $isPayed = null): array
    {
        $query = $this->createQueryBuilder('i');

        $query = $this->addFilterPayedAt($query, $year, $quarter, $isPayed);

        return $query->getQuery()
            ->getResult();
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     * @param null $isPayed
     */
    private function addFilterPayedAt(QueryBuilder $query, int $year = null, int $quarter = null, $isPayed = null
    ): QueryBuilder {
        if ($year !== null) {
            $query->andWhere('ToChar(i.payedAt, \'YYYY\') = :year')
                ->setParameter('year', (string)$year);
        }

        if ($quarter !== null) {
            $query->andWhere('ToChar(i.payedAt, \'Q\') = :quarter')
                ->setParameter('quarter', (string)$quarter);
        }

        if ($isPayed !== null) {
            if ($isPayed) {
                $query->andWhere('i.payedAt IS NOT NULL');
            } else {
                $query->andWhere('i.payedAt IS NULL');
            }
        }

        return $query;
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     */
    private function addFilterCreatedAt(QueryBuilder $query, int $year = null, int $quarter = null
    ): QueryBuilder {
        if ($year !== null) {
            $query->andWhere('ToChar(i.createdAt, \'YYYY\') = :year')
                ->setParameter('year', (string)$year);
        }

        if ($quarter !== null) {
            $query->andWhere('ToChar(i.createdAt, \'Q\') = :quarter')
                ->setParameter('quarter', (string)$quarter);
        }

        return $query;
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     * @return array<Invoice>
     */
    public function getSalesRevenuesGroupBy(string $groupBy, int $year = null, int $quarter = null, bool $isPayed = true
    ): array {
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

        $query = $this->addFilterPayedAt($query, $year, $quarter, $isPayed);

        return $query->getQuery()->getResult();
    }

    public function getDaysCountByMonth(int $year): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('ToChar(i.createdAt, \'MM\') AS month')
            ->addSelect('SUM(i.daysCount) total')
            ->orderBy('month')
            ->groupBy('month');

        $query = $this->addFilterCreatedAt($query, $year);

        return array_map(
            function ($arr) {
                $arr['total'] = floatval($arr['total']);
                return $arr;
            },
            $query->getQuery()->getResult()
        );
    }

    public function getDaysCountByYears(): array
    {
        $query = $this->createQueryBuilder('i')
            ->select('ToChar(i.createdAt, \'YYYY\') AS year')
            ->addSelect('SUM(i.daysCount) total')
            ->orderBy('year')
            ->groupBy('year');

        return array_map(
            function ($arr) {
                $arr['total'] = floatval($arr['total']);
                return $arr;
            },
            $query->getQuery()->getResult()
        );
    }

    /**
     * @param int|null $year
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getDaysCountByYear(int $year = null): float
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalHt / i.tjm) total');

        $query = $this->addFilterCreatedAt($query, $year, null);

        return floatval($query->getQuery()->getSingleScalarResult());
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     * @param null $isPayed
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getSalesTaxesBy(int $year = null, int $quarter = null, $isPayed = null): int
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalTax) total');

        $query = $this->addFilterPayedAt($query, $year, $quarter, $isPayed);

        return intval($query->getQuery()->getSingleScalarResult());
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function remainingDaysBeforeTaxLimit(): float
    {
        $remainingRevenues = Invoice::LIMIT_AE_TVA - $this->getSalesRevenuesBy((new DateTime('now'))->format('Y'));

        return $remainingRevenues < 0 ? 0 : $remainingRevenues / Invoice::TJM_DEFAULT;
    }

    /**
     * @param int|null $year
     * @param int|null $quarter
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getSalesRevenuesBy(int $year = null, int $quarter = null, ?bool $isPayed = true): int
    {
        $query = $this->createQueryBuilder('i')
            ->select('SUM(i.totalHt) total');

        $query = $this->addFilterPayedAt($query, $year, $quarter, $isPayed);

        return intval($query->getQuery()->getSingleScalarResult());
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function remainingDaysBeforeLimit(): float
    {
        $remainingRevenues = Invoice::LIMIT_AE - $this->getSalesRevenuesBy((new DateTime('now'))->format('Y'));

        return $remainingRevenues < 0 ? 0 : $remainingRevenues / Invoice::TJM_DEFAULT;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isOutOfTaxLimit($newInvoiceAmount = 0): bool
    {
        return ($this->getSalesRevenuesBy() + $newInvoiceAmount) >= Invoice::LIMIT_AE_TVA;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function isOutOfLimit($newInvoiceAmount = 0): bool
    {
        return ($this->getSalesRevenuesBy() + $newInvoiceAmount) >= Invoice::LIMIT_AE;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function countWaiting(): int
    {
        $query = $this->createQueryBuilder('i')
            ->select('COUNT(i.id) waitingCount')
            ->where('i.status = :waiting')
            ->setParameter('waiting', InvoiceStatusEnum::Waiting);

        return $query->getQuery()->getSingleScalarResult();
    }
}
