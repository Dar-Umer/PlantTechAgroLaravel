<?php

namespace App\Services;

use App\Models\PosSale;

class PosInvoiceNumberer
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
        $prefix = 'POS';
        $fy = self::fiscalYear();

        $seq = PosSale::where('invoice_number', 'like', $prefix.'/'.$fy.'/%')->count() + 1;

        for ($attempt = 0; $attempt < 30; $attempt++) {
            $number = sprintf('%s/%s/%04d', $prefix, $fy, $seq);

            if (! PosSale::where('invoice_number', $number)->exists()) {
                return $number;
            }

            $seq++;
        }

        return $prefix.'/'.$fy.'/'.strtoupper(uniqid());
    }
}
