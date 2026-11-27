<?php

namespace App\Support;

class PhoneNormalizer
{
    public static function normalize(?string $phone): ?string
    {
        if ($phone === null) {
            return null;
        }

        $trimmed = trim($phone);
        if ($trimmed === '' || in_array($trimmed, ['+992', '992', '+7', '+996', '+998'], true)) {
            return null;
        }

        $cleaned = preg_replace('/[^0-9+]/', '', $trimmed) ?? '';
        if ($cleaned === '' || in_array($cleaned, ['+992', '992', '+7', '+996', '+998'], true)) {
            return null;
        }

        if (preg_match('/^\+(992\d{9}|7\d{10}|996\d{9}|998\d{9})$/', $cleaned)) {
            return $cleaned;
        }

        if (str_starts_with($cleaned, '+992')) {
            return null;
        }

        if (str_starts_with($cleaned, '992')) {
            $digits = substr($cleaned, 3);
            return preg_match('/^\d{9}$/', $digits) ? '+' . $cleaned : null;
        }

        if (str_starts_with($cleaned, '0')) {
            $digits = substr($cleaned, 1);
            return preg_match('/^\d{9}$/', $digits) ? '+992' . $digits : null;
        }

        if (preg_match('/^\d{9}$/', $cleaned)) {
            return '+992' . $cleaned;
        }

        $digitsOnly = preg_replace('/\D/', '', $cleaned) ?? '';
        if (strlen($digitsOnly) >= 9) {
            return '+992' . substr($digitsOnly, -9);
        }

        return null;
    }

    public static function hasDigits(?string $phone): bool
    {
        if ($phone === null) {
            return false;
        }

        $trimmed = trim($phone);
        if ($trimmed === '' || in_array($trimmed, ['+992', '992', '+7', '+996', '+998'], true)) {
            return false;
        }

        return preg_match('/\d/', $trimmed) === 1;
    }
}
