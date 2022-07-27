<?php
namespace App\Form\Filter;

use App\Enum\SkillTypeEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SkillTypeFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => SkillTypeEnum::values(),
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}