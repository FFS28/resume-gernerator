<?php
namespace App\Form\Filter;

use App\Enum\InvoiceStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class InvoiceStatusFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => InvoiceStatusEnum::values(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}