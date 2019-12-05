<?php

namespace App\Form\Type;

use App\Entity\Activity;
use App\Entity\Invoice;
use DateInterval;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthActivitiesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /** @var \DateTime $currentDate */
        $currentDate = $options['currentDate'];
        /** @var Activity[] $activities */
        $activities = $options['activities'];
        /** @var Invoice $invoice */
        $invoice = $options['invoice'];

        $activitiesData = [];
        for($i = 1; $i < $currentDate->format('N'); $i++) {
            $activitiesData[] = [
                'selected' => false,
                'date' => null,
                'value' => null
            ];
        }

        for ($i = 1; $i <= $currentDate->format('t'); $i++) {
            $date = $currentDate->format('Ymd');

            $activitiesData[$date] = [
                'selected' => false,
                'date' => new \DateTime('now'),
                'value' => 0,
                'invoice' => $invoice
            ];
            $currentDate->add(new DateInterval('P1D'));
        }

        foreach ($activities as $activity) {
            $date = $activity->getDate()->format('Ymd');

            if (isset($activitiesData[$date])) {
                $activitiesData[$date]['value'] = $activity->getValue();
                $activitiesData[$date]['selected'] = true;
            }
        }
        
        $builder
            ->add('activities', CollectionType::class, [
                'entry_type' => ActivityType::class,
                'data' => array_values($activitiesData)
            ])
            ->add('send', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'invoice' => null,
            'activities' => [],
            'currentDate' => null
        ]);
    }
}
