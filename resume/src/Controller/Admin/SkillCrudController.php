<?php

namespace App\Controller\Admin;

use App\Entity\Skill;
use App\Enum\DeclarationTypeEnum;
use App\Enum\SkillTypeEnum;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\NumberField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\EnumType;

class SkillCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Skill::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Skill')
            ->setEntityLabelInPlural('Skills')
            ->setDefaultSort(['onHomepage' => 'DESC', 'level' => 'DESC'])
            ->setSearchFields(['name', 'type', 'level'])
            ;
    }


    public function configureFields(string $pageName): iterable
    {
        yield FormField::addPanel('Informations');
        yield TextField::new('name')->setColumns(2);

        if (Crud::PAGE_INDEX === $pageName) {
            yield ChoiceField::new('type')
                ->setTranslatableChoices([...SkillTypeEnum::choices()]);
            yield NumberField::new('level');

        } elseif (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield ChoiceField::new('type')->setColumns(2)
                ->setChoices([...SkillTypeEnum::cases()])
                ->setFormType(EnumType::class)
                ->setFormTypeOptions(['class' => SkillTypeEnum::class]);
            yield NumberField::new('level')->setFormTypeOption('attr', ['min' => 0, 'max' => 10])->setColumns(1);
        }

        yield AssociationField::new('parent')->setColumns(2);
        yield BooleanField::new('onHomepage')->setColumns(12);

        if (Crud::PAGE_EDIT === $pageName || Crud::PAGE_NEW === $pageName) {
            yield FormField::addPanel('Experiences');
            yield ArrayField::new('experiences')
                ->setLabel(false)
                ->setDisabled()
                ->setFormTypeOption('allow_add', false)
                ->setFormTypeOption('allow_delete', false)
            ;
        }
    }
}
