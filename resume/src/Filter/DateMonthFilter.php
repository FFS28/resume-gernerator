<?php
namespace App\Filter;

use App\Form\Filter\DateMonthFilterType;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class DateMonthFilter implements FilterInterface
{
    use FilterTrait;
    static string $fieldName;
    static string $fieldNameAlias;

    public static function new(string $fieldName, $label = null): self
    {
        self::$fieldName = $fieldName;
        self::$fieldNameAlias = $fieldName.'Month';
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty(self::$fieldNameAlias)
            ->setLabel($label)
            ->setFormType(DateMonthFilterType::class)
        ;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        $queryBuilder
            ->andWhere(sprintf('ToChar(%s.%s, \'MM\') = :%s', $filterDataDto->getEntityAlias(), self::$fieldName, self::$fieldNameAlias))
            ->setParameter(self::$fieldNameAlias, $filterDataDto->getValue())
        ;
    }
}