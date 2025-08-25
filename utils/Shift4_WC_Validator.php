<?php 
declare(strict_types=1);

namespace Shift4\WooCommerce\Utils;

if (!defined('ABSPATH')) exit;

class Shift4_WC_Validator {
    const RANDOM_PART_CHARS = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    
    public static function isValidToken(string $token) : bool {
        if (strpos($token, 'tok_') !== 0) {
            return false;
        }

        $rest = substr($token, 4);
        $isValid = true;
        for ($i = 0; $i < strlen($rest); $i++) {
            if (!in_array($rest[$i], self::RANDOM_PART_CHARS)) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }

    public static function validateToken(string $token): string {
        $isValid = self::isValidCardToken($token);

        if (!$isValid) {
            throw new Exception('Invalid card token format');
        }

        return $token;
    }
}
