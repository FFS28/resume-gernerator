<?php

namespace App\Form\Type;

use App\Entity\Ingredient;
use App\Entity\RecipeIngredient;
use EasyCorp\Bundle\EasyAdminBundle\Form\Type\EasyAdminAutocompleteType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RecipeIngredientType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', HiddenType::class)
            ->add('ingredient', EasyAdminAutocompleteType::class, [
                'class' => Ingredient::class
            ])
            ->add('quantity', NumberType::class, [
                'attr' => ['class' => 'field-quantity', 'min' => 0, 'step' => 0.1],
                'scale' => 1,
                'html5' => true
            ])
            ->add('unit', ChoiceType::class, [
                'multiple' => false,
                'expanded' => true,
                'choices' => array_merge(['' => ''], RecipeIngredient::UNITS)
            ])
            ->add('measure')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => null
        ]);
    }
}
