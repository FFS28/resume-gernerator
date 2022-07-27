<?php
namespace App\Form\Filter;

use App\Enum\CompanyTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CompanyTypeFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => CompanyTypeEnum::values(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}