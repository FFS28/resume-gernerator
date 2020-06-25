<?php
namespace App\Form\Filter;

use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateYearFilterType extends FilterType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $currentYear = intval((new \DateTime('now'))->format('Y'));
        $values = array_reverse(range($currentYear - 20, $currentYear));
        $choices = array_combine($values, $values);
        $resolver->setDefaults([
            'choices' => $choices,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {
        $queryBuilder->andWhere('ToChar(entity.'.$metadata['type_options']['data'].', \'YYYY\') = :year')
            ->setParameter('year', (string) $form->getData());
    }
}