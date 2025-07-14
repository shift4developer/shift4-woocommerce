<?php

function shift4_log($messages) {
    if (!function_exists('wc')) {
        wp_die();
    }

    $hash = null;
    $cart_hash = 'empty';

    $cart = WC()->cart;
    if ($cart) {
        $hash = WC()->session->get_customer_id();
        $cart_hash = $cart->get_cart_hash();
    }

    if ($hash) {
        $prefix = 'shift4-js-' . $hash;
    } else {
        $prefix = 'shift4-js-no-hash';
    }

    if (is_array($messages)) {
        for($i = 0; $i < count($messages); $i++) {
            $message = $messages[$i];
            wc_get_logger()->info(
                '[' . $cart_hash . '] ' . sanitize_text_field($message, true),
                ['source' => $prefix]
            );
        }
    }
}

function shift4_log_be($message) {
    shift4_log(['[BE] ' . $message]);
}