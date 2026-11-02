<?php

declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/**
 * Helper aritmetika uang berbasis BCMath.
 *
 * Nilai uang selalu berupa string desimal skala 2 (mis. "1500.00").
 * Float dilarang di seluruh aplikasi, karena binary floating point tidak
 * dapat merepresentasikan desimal secara eksak.
 */
final class Money
{
    public const SCALE = 2;

    private function __construct()
    {
        //
    }

    public static function add(string|int $a, string|int $b): string
    {
        return bcadd(self::normalize($a), self::normalize($b), self::SCALE);
    }

    public static function sub(string|int $a, string|int $b): string
    {
        return bcsub(self::normalize($a), self::normalize($b), self::SCALE);
    }

    public static function compare(string|int $a, string|int $b): int
    {
        return bccomp(self::normalize($a), self::normalize($b), self::SCALE);
    }

    /**
     * Format tampilan lokal Indonesia: 1234567.5 menjadi "1.234.567,50".
     */
    public static function format(string|int $value): string
    {
        $normalized = self::normalize($value);
        $negative = str_starts_with($normalized, '-');

        [$whole, $fraction] = explode('.', ltrim($normalized, '-'));

        $grouped = strrev(implode('.', str_split(strrev($whole), 3)));

        return ($negative ? '-' : '').$grouped.','.$fraction;
    }

    /**
     * Normalisasi ke string desimal skala 2 tanpa melewati float.
     */
    private static function normalize(string|int $value): string
    {
        if (is_int($value)) {
            $value = (string) $value;
        }

        if (preg_match('/^-?\d+(\.\d+)?$/', $value) !== 1) {
            throw new InvalidArgumentException('Nilai uang harus berupa string desimal, bukan float.');
        }

        return bcadd($value, '0', self::SCALE);
    }
}
