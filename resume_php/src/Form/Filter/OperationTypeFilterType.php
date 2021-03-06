<?php
namespace App\Form\Filter;

use App\Entity\Company;
use App\Entity\Declaration;
use App\Entity\Invoice;
use App\Entity\Operation;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationTypeFilterType extends FilterType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $types = Operation::TYPES;
        array_unshift($types, ["" => null]);
        $resolver->setDefaults([
            'choices' => $types,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {
        if ($form->getData()) {
            $queryBuilder->andWhere('entity.type = :type')
                ->setParameter('type', (string)$form->getData());
        }
        else {
            $queryBuilder->andWhere('entity.type IS NULL');
        }
    }
}
