<?php

namespace App\Enum;

use Traversable;

enum PersonCivilityEnum: string
{
    case Men = 'h';
    case Women = 'f';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Men => 'Men',
            self::Women => 'Women',
        };
    }

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }

    public static function values(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->toString() => $case->value;
        }
    }
}