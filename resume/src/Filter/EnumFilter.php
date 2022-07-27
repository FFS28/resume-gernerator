<?php
namespace App\Filter;

use App\Enum\InvoiceStatusEnum;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Filter\FilterInterface;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FieldDto;
use EasyCorp\Bundle\EasyAdminBundle\Dto\FilterDataDto;
use EasyCorp\Bundle\EasyAdminBundle\Filter\FilterTrait;

class EnumFilter implements FilterInterface
{
    use FilterTrait;

    public static function new(string $propertyName, string $formTypeName, $label = null): self
    {
        return (new self())
            ->setFilterFqcn(self::class)
            ->setProperty($propertyName)
            ->setLabel($label)
            ->setFormType($formTypeName)
        ;
    }

    public function apply(QueryBuilder $queryBuilder, FilterDataDto $filterDataDto, ?FieldDto $fieldDto, EntityDto $entityDto): void
    {
        if ($filterDataDto->getValue() !== null) {
            $queryBuilder
                ->andWhere(
                    sprintf(
                        '%s.%s = :%s', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty(),
                        $filterDataDto->getProperty()
                    )
                )
                ->setParameter($filterDataDto->getProperty(), $filterDataDto->getValue());
        } else {
            $queryBuilder
                ->andWhere(
                    sprintf(
                        '%s.%s IS NULL', $filterDataDto->getEntityAlias(), $filterDataDto->getProperty()
                    )
                );
        }
    }
}