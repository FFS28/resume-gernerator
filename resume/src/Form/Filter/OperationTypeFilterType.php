<?php
namespace App\Form\Filter;

use App\Enum\OperationTypeEnum;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationTypeFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $types = iterator_to_array(OperationTypeEnum::values());
        array_unshift($types, ['' => null]);

        $resolver->setDefaults([
            'choices' => $types,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}
