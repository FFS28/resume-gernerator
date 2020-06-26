<?php
namespace App\Form\Filter;

use App\Entity\Company;
use App\Entity\Invoice;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Form\Filter\Type\FilterType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceStatusFilterType extends FilterType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => Invoice::STATUES,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function filter(QueryBuilder $queryBuilder, FormInterface $form, array $metadata)
    {
        $queryBuilder->andWhere('entity.type = :type')
            ->setParameter('type', (string) $form->getData());
    }
}