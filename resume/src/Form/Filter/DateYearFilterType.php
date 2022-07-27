<?php
namespace App\Form\Filter;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateYearFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $currentYear = intval((new \DateTime('now'))->format('Y'));
        $values = array_reverse(range(2015, $currentYear));
        $choices = array_combine($values, $values);

        $resolver->setDefaults([
            'choices' => $choices,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}