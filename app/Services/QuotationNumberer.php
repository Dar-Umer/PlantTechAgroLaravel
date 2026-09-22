<?php

namespace App\Services;

use App\Models\Quotation;

class QuotationNumberer
{
    public static function fiscalYear(): string
    {
        $now = now();

        return $now->month >= 4
            ? $now->format('Y').'-'.$now->copy()->addYear()->format('y')
            : $now->copy()->subYear()->format('Y').'-'.$now->format('y');
    }

    public static function next(): string
    {
        $prefix = trim((string) config('quotation.prefix', 'QT'), '/') ?: 'QT';
        $fy = self::fiscalYear();

        $seq = Quotation::where('number', 'like', $prefix.'/'.$fy.'/%')->count() + 1;

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $number = sprintf('%s/%s/%04d', $prefix, $fy, $seq);

            if (! Quotation::where('number', $number)->exists()) {
                return $number;
            }

            $seq++;
        }

        return $prefix.'/'.$fy.'/'.uniqid();
    }
}
