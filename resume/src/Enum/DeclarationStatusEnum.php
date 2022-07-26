<?php

namespace App\Enum;

use Traversable;

enum DeclarationStatusEnum: string
{
    case Waiting = 'waiting';
    case Payed = 'payed';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
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