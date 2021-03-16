<?php

namespace App\Validator;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class InvoiceTax extends Constraint
{
    /*
     * Any public properties become valid options for the annotation.
     * Then, use these in your validator class.
     */
    public $message_empty = 'Tax should not be filled.';
    public $message_fill = 'Tax must be completed.';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
