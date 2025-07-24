<?php

namespace Shift4\WooCommerce\Gateway;

use WC_Order;
use Shift4\Exception\Shift4Exception;

class FraudHandler
{
    public static function handleFraud(Shift4Exception $e, WC_Order $order, string $action): void
    {
        $type = $e->getType();
        $code = $e->getCode();
        $types = [
            'invalid_request',
            'card_error',
            'rate_limit_error',
        ];

        if (in_array($type, $types, true)) {
            $codes = [
                'lost_or_stolen',
                'suspected_fraud',
                'card_declined',
                'blocked',
                'call_issuer',
                'limit_exceeded',
                'do_not_try_again',
                'payment_method_declined',
            ];

            if ($type !== 'card_error' || in_array($code, $codes, true)) {
                switch ($action) {
                    case 'cancel_order':
                        $order->update_status('wc-cancelled', __('Order cancelled due to fraud detected.', 'shift4-for-woocommerce'));
                        break;
                    case 'move_to_trash':
                        $order->update_status('trash', __('Order trashed due to fraud detected.', 'shift4-for-woocommerce'));
                        break;
                    case 'standard':
                    default:
                        break;
                }
            }
        }
    }
}