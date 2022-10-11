<?php

namespace App\Helper;

class DateHelper
{
    public static function lastDayOfMonth(\DateTime $date): \DateTime
    {
        return $date->modify('last day of');
    }
}