<?php
namespace App\Form\Filter;

use DateTime;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateMonthFilterType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        $monthNumber = range(1, 12);
        $values = array_map(fn($item) => (string)(intval($item < 10) ? '0' : '').$item, $monthNumber);
        $keys = array_map(function($item) {
            $date = DateTime::createFromFormat('!m', $item);
            return $date->format('F');
        }, $monthNumber);
        $choices = array_combine($keys, $values);

        $resolver->setDefaults([
            'choices' => $choices,
        ]);
    }

    public function getParent(): string
    {
        return ChoiceType::class;
    }
}