<?php
namespace App\Form\Filter;

use App\Enum\DeclarationTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeclarationTypeFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => DeclarationTypeEnum::values(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}