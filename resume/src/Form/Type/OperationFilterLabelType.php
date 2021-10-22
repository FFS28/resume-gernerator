<?php
namespace App\Form\Type;

use App\Entity\OperationFilter;
use App\Repository\OperationFilterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OperationFilterLabelType extends AbstractType {
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(OperationFilter::class);
    }

    public function getParent()
    {
        return TextType::class;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $labelsAndTypes = $this->repository->getLabelsAndTypes();
        $view->vars['choices'] = $labelsAndTypes;
    }

    public function getName()
    {
        return 'operation_filter_label';
    }
}
