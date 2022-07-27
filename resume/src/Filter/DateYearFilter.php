<?php
namespace App\Filter;

use App\Form\Filter\DateYearFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class DateYearFilter implements FilterInterface
{
    use FilterTrait;
    static string $fieldName;
    static string $fieldNameAlias;

    public static function new(string $fieldName, $label = null): self
    {
        self::$fieldName = $fieldName;
        self::$fieldNameAlias = $fieldName.'Year';
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty(self::$fieldNameAlias)
            ->setLabel($label)
            ->setFormType(DateYearFilterType::class)
        ;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $queryBuilder
            ->andWhere(sprintf('ToChar(%s.%s, \'YYYY\') = :%s', $filterDataDto->getEntityAlias(), self::$fieldName, self::$fieldNameAlias))
            ->setParameter(self::$fieldNameAlias, $filterDataDto->getValue())
        ;
    }
}