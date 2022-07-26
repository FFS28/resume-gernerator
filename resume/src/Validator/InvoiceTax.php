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
    public readonly string $message_empty;

    public readonly string $message_fill;

    public function __construct(mixed $options = null, array $groups = null, mixed $payload = null)
    {
        $this->message_fill = 'Tax must be completed.';
        $this->message_empty = 'Tax should not be filled.';
        parent::__construct($options, $groups, $payload);
    }

    public function getTargets(): array|string
    {
        return self::CLASS_CONSTRAINT;
    }
}
