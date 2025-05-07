<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;
use Shift4\WooCommerce\Gateway\Card;

class WC_Shift4_Block_Support extends AbstractPaymentMethodType
{

    public function __construct(private Card $cardGateway,)
    {
        $this->cardGateway = $cardGateway;
    }

    /**
     * Payment method name defined by payment methods extending this class.
     *
     * @var string
     */
    protected $name = 'shift4';

    /**
     * Initializes the payment method type.
     */
    public function initialize()
    {
        $this->settings = get_option('woocommerce_shift4_shared_settings', []);
    }

    /**
     * Returns an array of scripts/handles to be registered for this payment method.
     *
     * @return array
     */
    public function get_payment_method_script_handles()
    {
        wp_enqueue_script(
            'wc-shift4-blocks-integration',
            plugins_url('build/index.js', __FILE__),
            ['wc-blocks-checkout'],
            '1.0',
            true
        );

        wp_enqueue_script(
            'applepay-button-client',
            'https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js'
        );

        $data = $this->get_payment_method_data();
        $shift4Config = [
            'blogName' => get_bloginfo('name'),
            'threeDS' => $data['3ds_mode'],
            'threeDSValidationMessage' => __('3DS validation failed.', 'shift4'),
            'publicKey' => $data['publicKey'],
            'savedCardsEnabled' => $data['savedCardsEnabled'],
        ];

        wp_localize_script(
            'wc-shift4-blocks-integration',
            'shift4Config',
            $shift4Config
        );

        return ['wc-shift4-blocks-integration'];
    }

    /**
     * Returns if this payment method should be active. If false, the scripts will not be enqueued.
     *
     * @return boolean
     */
    public function is_active()
    {
        return true;
    }

    /**
     * Returns an array of key=>value pairs of data made available to the payment methods script.
     *
     * @return array
     */
    public function get_payment_method_data()
    {
        $gateway = $this->cardGateway;
        return [
            'title'         => $gateway->method_title,
            'description'   => $gateway->method_description,
            'supports'      => $gateway->supports,
            '3ds_mode'      => $gateway->settings['3ds_mode'],
            'publicKey'     => $gateway->settings['shared_public_key'],
            'savedCardsEnabled' => $gateway->settings['saved_cards_enabled'],
        ];
    }
}
