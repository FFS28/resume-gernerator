<?php
namespace App\Form\Type;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Doctrine\DBAL\Types\Type;

class DatalistType extends AbstractType
{
    public function getParent(): string
    {
        return EntityType::class;
    }
}
