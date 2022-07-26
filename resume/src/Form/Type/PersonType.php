<?php

namespace App\Form\Type;

use App\Entity\Company;
use App\Enum\PersonCivilityEnum;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PersonType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('company', EntityType::class, ['class' => Company::class])
            ->add('civility', ChoiceType::class, ['choices' => PersonCivilityEnum::choices()])
            ->add('lastname')
            ->add('firstname')
            ->add('emails', CollectionType::class)
            ->add('phones', CollectionType::class)
            ->add('isInvoicingDefault', CheckboxType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([]);
    }
}
