<?php

namespace App\ShopWare\Services;

class ShopwareApiHelper
{
    /**
     * Generate a Shopware-compatible ID (32-character hexadecimal string) from any input string
     *
     * @param string $input Any input string to generate ID from
     * @return string 32-character hexadecimal string
     */
    public static function generateId(string $input): string
    {
        return md5($input);
    }

    /**
     * Check if a string is a valid Shopware ID
     *
     * @param string $id The ID to validate
     * @return bool True if valid, false otherwise
     */
    public static function isValidId(string $id): bool
    {
        return (bool) preg_match('/^[0-9a-f]{32}$/', $id);
    }
}
