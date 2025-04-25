<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

class WC_Shift4_Block_Support extends AbstractPaymentMethodType
{

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
            plugins_url('assets/js/blocks.js', __FILE__),
            ['wc-blocks-checkout'],
            '1.0',
            true
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
        return [
            'title'       => 'Shift4 Block Gateway',
            'description' => 'Shift4 Block Gateway',
            'supports'    => ['products'],
        ];
    }
}
