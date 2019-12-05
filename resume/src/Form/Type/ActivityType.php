<?php

namespace App\Form\Type;

use App\Entity\Invoice;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ActivityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('selected', CheckboxType::class, [
                'required' => false,
            ])
            ->add('value', NumberType::class, [
                'html5' => true,
                'attr' => [
                    'max'=>"1",
                    'min'=>"0",
                    'step'=>"0.5"
                ],
                'required' => false,
            ])
            ->add('date', DateType::class, [
                'data' => null,
                'required' => false,
                'attr' => [
                    'class' => 'hidden'
                ]
            ])
            ->add('invoice', EntityType::class, [
                'class' => Invoice::class,
                'required' => false,
                'attr' => [
                    'class' => 'hidden'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null,
            'date' => null
        ]);
    }
}
