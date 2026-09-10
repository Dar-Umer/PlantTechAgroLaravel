<?php

namespace App\Support;

class Format
{
    public static function qty(mixed $value): string
    {
        $value = (float) $value;

        if ($value == 0) {
            return '0';
        }

        return rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
    }
}