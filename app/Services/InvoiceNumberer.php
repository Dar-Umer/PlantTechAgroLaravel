<?php

namespace App\Services;

use App\Models\Invoice;

class InvoiceNumberer
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
        $prefix = trim((string) config('invoice.prefix', 'PTA'), '/') ?: 'PTA';
        $fy = self::fiscalYear();

        $seq = Invoice::where('number', 'like', $prefix.'/'.$fy.'/%')->count() + 1;

        for ($attempt = 0; $attempt < 25; $attempt++) {
            $number = sprintf('%s/%s/%04d', $prefix, $fy, $seq);

            if (! Invoice::where('number', $number)->exists()) {
                return $number;
            }

            $seq++;
        }

        return $prefix.'/'.$fy.'/'.uniqid();
    }
}
