<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

if (!defined('ABSPATH')) exit;

class CaptureStrategySource
{
    public const MODE_CAPTURE = 'capture';
    public const MODE_AUTH = 'auth';

    public static function options(): array
    {
        return [
            self::MODE_CAPTURE => __('Capture', 'shift4-for-woocommerce'),
            self::MODE_AUTH => __('Authorise Only', 'shift4-for-woocommerce'),
        ];
    }
}
