<?php

namespace App\Service;

final class Utf8Sanitizer
{
    public static function clean(?string $value): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        if (!mb_check_encoding($value, 'UTF-8')) {
            $converted = @mb_convert_encoding($value, 'UTF-8', 'Windows-1252');
            if ($converted !== false && mb_check_encoding($converted, 'UTF-8')) {
                $value = $converted;
            }
        }

        if (function_exists('mb_scrub')) {
            return mb_scrub($value, 'UTF-8');
        }

        $converted = @mb_convert_encoding($value, 'UTF-8', 'UTF-8');

        return $converted !== false ? $converted : '';
    }
}
