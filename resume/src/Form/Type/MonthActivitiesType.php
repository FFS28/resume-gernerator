<?php

namespace App\Form\Type;

use App\Entity\Activity;
use App\Entity\Company;
use App\Service\ReportService;
use DateTime;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MonthActivitiesType extends AbstractType
{
    public DateTime $currentDate;

    public ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * @throws Exception
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $this->currentDate = $options['currentDate'];
        /** @var Activity[] $activities */
        $activities = $options['activities'];
        /** @var Company $company */
        $company = $options['company'];

        $currentDate = clone $this->currentDate;
        $activitiesData = $this->reportService->generateMonth(clone $currentDate, $activities, $company);

        $builder
            ->add('activities', CollectionType::class, [
                'entry_type' => ActivityType::class,
                'data'       => array_values($activitiesData)
            ])
            ->add('send', SubmitType::class, [
                'attr' => [
                    'class' => 'btn btn-primary action-save'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                                   'activities'  => [],
                                   'currentDate' => null,
                                   'company'     => null
                               ]);
    }
}
