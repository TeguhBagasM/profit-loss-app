<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Proteksi formula injection pada sel teks Excel/CSV.
 */
final class ExcelSanitizer
{
    private function __construct()
    {
        //
    }

    /**
     * Beri awalan "'" bila teks dimulai dengan karakter yang bisa memicu formula.
     */
    public static function clean(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }
}
