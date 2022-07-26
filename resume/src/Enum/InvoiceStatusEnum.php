<?php

namespace App\Enum;

use Traversable;

enum InvoiceStatusEnum: string
{
    case Draft = 'draft';
    case Waiting = 'waiting';
    case Payed = 'payed';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Waiting => 'Waiting',
            self::Payed => 'Payed',
        };
    }

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}