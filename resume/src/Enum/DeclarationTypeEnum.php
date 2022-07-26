<?php

namespace App\Enum;

use Traversable;

enum DeclarationTypeEnum: string
{
    case TVA = 'tva';
    case Social = 'social';
    case Impot = 'impot';
    case CFE = 'cfe';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::TVA => 'TVA',
            self::Social => 'Social',
            self::Impot => 'Impot',
            self::CFE => 'CFE',
        };
    }

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}