<?php 
declare(strict_types=1);

namespace Shift4\WooCommerce\Utils;

if (!defined('ABSPATH')) exit;

class Shift4_WC_Validator {
    const RANDOM_PART_CHARS = [
        'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
        'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
        '0', '1', '2', '3', '4', '5', '6', '7', '8', '9'
    ];
    
    public static function isValidToken(string $token) : bool {
        if (strpos($token, 'tok_') !== 0) {
            return false;
        }

        $rest = substr($token, 4);
        $isValid = true;
        for ($i = 0; $i < strlen($rest); $i++) {
            if (!in_array($rest[$i], self::RANDOM_PART_CHARS, true)) {
                $isValid = false;
                break;
            }
        }
        return $isValid;
    }

    public static function validateToken(string $token): string {
        $isValid = self::isValidToken($token);

        if (!$isValid) {
            throw new \Exception('Invalid card token format');
        }

        return $token;
    }
}
