<?php

namespace App\Support;

class Phone
{
    public static function digits(string $value): string
    {
        return preg_replace('/[^\d]/', '', $value) ?? '';
    }
}