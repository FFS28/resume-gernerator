<?php

namespace App\Controller\Admin;

use App\Entity\Operation;
use App\Entity\OperationFilter;
use App\Enum\OperationTypeEnum;
use App\Filter\DateMonthFilter;
use App\Filter\DateYearFilter;
use App\Filter\EnumFilter;
use App\Form\Filter\OperationTypeFilterType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\EntityDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function Symfony\Component\Translation\t;

class OperationFilterCrudController extends AbstractCrudController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TranslatorInterface $translator,
    )
    {
    }

    public static function getEntityFqcn(): string
    {
        return OperationFilter::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Filter')
            ->setEntityLabelInPlural('Filters')
            ->setDefaultSort(['type' => 'ASC'])
            ->setSearchFields(['name', 'type', 'label', 'target', 'date', 'amount'])
            ;
    }


    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add('id')
            ->add('name')
            ->add(EnumFilter::new('type', OperationTypeFilterType::class))
            ->add('label')
            ->add('target')
            ->add(DateYearFilter::new('date', 'Created At year'))
            ->add(DateMonthFilter::new('date', 'Created At month'))
        ;
    }

    public function configureFields(string $pageName): iterable
    {
        if (Crud::PAGE_INDEX === $pageName) {
            yield NumberField::new('id');
        }

        yield FormField::addPanel('Operation');
        yield TextField::new('name');

        yield FormField::addPanel('Categories');
        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...OperationTypeEnum::choices()]);
            yield TextField::new('label');

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield ChoiceField::new('type')->setColumns(2)
                ->setChoices([...OperationTypeEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => OperationTypeEnum::class]);

            $operationFilterRepository = $this->entityManager->getRepository(OperationFilter::class);

            $lablesAndTypes = $operationFilterRepository->getLabelsAndTypes();
            $choices = array_map(fn($labelAndType): string =>
                $this->translator->trans($labelAndType['type']) . ' / ' . $labelAndType['label']
                , $lablesAndTypes);
            sort($choices);

            yield ChoiceField::new('labelAutocomplete', $this->translator->trans('Label Autocomplete'))->setColumns(2)
                ->setChoices(array_combine($choices, $choices))
                ->setFormTypeOption('translation_domain', false)
            ;
            yield TextField::new('labelCustom')->setColumns(2);
        }

        yield FormField::addPanel('Informations');
        yield TextField::new('target')->setColumns(2);
        yield DateField::new('date')->setColumns(2);
        yield MoneyField::new('amount')->setColumns(2)->setCurrency('EUR')->setStoredAsCents(false)->setNumDecimals(2);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield BooleanField::new('hasDuplicate')->setColumns(2);
        }
    }

    public function createEditForm(EntityDto $entityDto, KeyValueStore $formOptions, AdminContext $context): FormInterface
    {
        $entityInstance = $entityDto->getInstance();
        $entityInstance->labelAutocomplete = $this->translator->trans($entityInstance->getType()->value) . ' / ' . $entityInstance->getLabel();
        $entityDto->setInstance($entityInstance);

        return parent::createEditForm($entityDto, $formOptions, $context);
    }

    private function updateLabel(OperationFilter $operationFilter)
    {
        if ($operationFilter->labelCustom) {
            $operationFilter->setLabel($operationFilter->labelCustom);
        } elseif ($operationFilter->labelAutocomplete) {
            $infos = explode(' / ', $operationFilter->labelAutocomplete);
            if (count($infos) == 2) {
                $operationFilter->setLabel($infos[1]);
            }
        }
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updateLabel($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->updateLabel($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }
}
