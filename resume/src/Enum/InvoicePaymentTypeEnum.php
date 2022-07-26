<?php

namespace App\Enum;

use Traversable;

enum InvoicePaymentTypeEnum: string
{
    case Check = 'check';
    case Transfert = 'transfert';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Check => 'Check',
            self::Transfert => 'Transfert',
        };
    }

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}