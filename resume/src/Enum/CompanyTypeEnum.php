<?php

namespace App\Enum;

use JsonSerializable;
use Traversable;

enum CompanyTypeEnum: string implements JsonSerializable
{
    case Client = 'client';
    case Prospect = 'prospect';
    case Archive = 'archive';
    case ESN = 'esn';
    case Company = 'company';

    public function jsonSerialize(): string
    {
        return $this->toString();
    }

    public function toString(): string
    {
        return match ($this) {
            self::Client => 'Client',
            self::Prospect => 'Prospect',
            self::Archive => 'Archive',
            self::ESN => 'ESN',
            self::Company => 'Company',
        };
    }

    public static function choices(): Traversable
    {
        foreach (self::cases() as $case) {
            yield $case->value => $case->toString();
        }
    }
}