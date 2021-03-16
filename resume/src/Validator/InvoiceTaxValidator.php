<?php

namespace App\Validator;

use App\Repository\InvoiceRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class InvoiceTaxValidator extends ConstraintValidator
{
    /** @var InvoiceRepository */
    private $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function validate($value, Constraint $constraint)
    {
        /* @var $constraint  InvoiceTax */

        $isOutOfTaxLimt = $this->invoiceRepository->isOutOfTaxLimit();

        if ($isOutOfTaxLimt && $value || !$isOutOfTaxLimt && !$value) {
            return;
        }

        $this->context->buildViolation($isOutOfTaxLimt ? $constraint->message_fill : $constraint->message_empty)
            ->setParameter('{{ value }}', $value)
            ->addViolation();
    }
}
