<?php

namespace App\Form\Type;

use App\Entity\Activity;
use App\Entity\Company;
use App\Entity\Invoice;
use App\Repository\InvoiceRepository;
use DateInterval;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthActivitiesType extends AbstractType
{
    /** @var \DateTime $currentDate */
    public $currentDate;

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->currentDate = $options['currentDate'];
        /** @var Activity[] $activities */
        $activities = $options['activities'];
        /** @var Company $company */
        $company = $options['company'];

        $activitiesData = [];
        for($i = 1; $i < $this->currentDate->format('N'); $i++) {
            $activitiesData[] = [
                'selected' => false,
                'date' => null,
                'value' => null
            ];
        }

        $currentDate = clone $this->currentDate;
        for ($i = 1; $i <= $this->currentDate->format('t'); $i++) {
            $date = $currentDate->format('Ymd');

            $activitiesData[$date] = [
                'selected' => false,
                'date' => clone $currentDate,
                'value' => 1,
                'company' => $company
            ];
            $currentDate->add(new DateInterval('P1D'));
        }

        foreach ($activities as $activity) {
            $date = $activity->getDate()->format('Ymd');

            if (isset($activitiesData[$date])) {
                $activitiesData[$date]['value'] = $activity->getValue();
                $activitiesData[$date]['company'] = $activity->getCompany();
                $activitiesData[$date]['selected'] = true;
            }
        }
        
        $builder
            ->add('activities', CollectionType::class, [
                'entry_type' => ActivityType::class,
                'data' => array_values($activitiesData)
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary action-save'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'activities' => [],
            'currentDate' => null,
            'company' => null
        ]);
    }
}
