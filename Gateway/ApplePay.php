<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway;

use Shift4\Exception\Shift4Exception;
use Shift4\Request\PaymentMethodRequest;
use Shift4\Request\PaymentMethodRequestApplePay;
use Shift4\WooCommerce\Gateway\Command\ChargeCommand;
use Shift4\WooCommerce\Gateway\Command\PaymentMethodCommand;
use Shift4\WooCommerce\Gateway\Command\RefundCommand;
use Shift4\WooCommerce\Model\ChargeRequestBuilder;
use Shift4\WooCommerce\Model\GatewayFactory;
use Shift4\WooCommerce\Model\Logger;

class ApplePay extends \WC_Payment_Gateway
{
    use SharedGatewayConfigTrait;
    use SharedGatewayLogic;

    public const ID = 'shift4_applepay';

    public function __construct(
        private ChargeRequestBuilder $chargeRequestBuilder,
        private PaymentMethodCommand $paymentMethodCommand,
        private ChargeCommand $chargeCommand,
        private RefundCommand $refundCommand,
        private Logger $logger,
    ) {
        $this->id = self::ID;
        $this->has_fields = true;
        $this->method_title = 'Shift4 (ApplePay)';
        $this->method_description = 'Take ApplePay payments via Shift4';
        $this->view_transaction_url = 'https://dev.shift4.com/charges/%s';
        $this->supports = [
            'products',
            'refunds',
        ];

        // Init fields and config, inc. shared credentials
        $this->initSharedFields();
        $this->initSharedConfig();
        $this->title = $this->get_option('title');

        // Register event to save payment settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields()
    {
        return [
            'method_header' => [
                'type' => 'shift4_config_section',
                'title' => 'Shift4 ApplePay Payments Configuration',
            ],
            'enabled' => [
                'title' => __('Enable/Disable', 'shift4-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable ApplePay', 'shift4-for-woocommerce'),
                'default' => 'no'
            ],
            'title' => [
                'title' => __('Title', 'shift4-for-woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'shift4-for-woocommerce'),
                'default' => __('ApplePay', 'shift4-for-woocommerce'),
                'desc_tip' => true,
            ],
        ];
    }

    public function payment_fields()
    {
        wp_enqueue_script(
            'shift4-js-client',
            'https://js.dev.shift4.com/shift4.js',
            [],
            SHIFT4_BUILD_HASH,
            false
        );
        wp_enqueue_script(
            'applepay-button-client',
            'https://applepay.cdn-apple.com/jsapi/v1/apple-pay-sdk.js',
            [],
            SHIFT4_BUILD_HASH,
            false
        );
        wc_get_template(
            'applepay-form.php',
            [
                'publicKey' => $this->getPublicKey(),
                'orderTotal' => $this->getOrderTotal(),
            ],
            'shift4',
            SHIFT4_PLUGIN_PATH . 'templates/'
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function validate_fields()
    {
        if (empty($_POST['shift4_applepay_token'])) {
            // Parent method says to return false but that doesn't abort the checkout process, an exception is needed
            $this->logger->debug(__METHOD__ . ' failed validation');
            throw new \Exception('Shift4 payment token missing');
        }
        $this->logger->debug(__METHOD__ . ' passed validation');
    }

    /**
     * @param $order_id
     * @return array
     * @throws \WC_Data_Exception
     */
    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        try {
            $paymentMethod = $this->paymentMethodCommand->execute();
            $chargeRequest = $this->chargeRequestBuilder->build($order, $this);
            $chargeRequest->paymentMethod($paymentMethod->getId());
            $charge = $this->chargeCommand->execute($chargeRequest);
            return $this->handleChargeResponse($order, $charge);

        } catch (Shift4Exception $e) {
            $this->logger->error(
                sprintf(
                    'Encountered `%s` while creating charge for %s. Request: %s, Error: %s',
                    get_class($e),
                    $order_id,
                    json_encode($chargeRequest->toArray()),
                    json_encode([
                        'type' => $e->getType(),
                        'code' => $e->getCode(),
                        'message' => $e->getMessage(),
                        'chargeId' => $e->getChargeId(),
                    ])
                ),
                [$chargeRequest, $e]
            );
            throw new \Exception(
                esc_html(
                    __(
                        'Sorry, we were unable to process your payment. Please check your card details are correct or try using a different payment method.',
                        'shift4-for-woocommerce'
                    )
                )
            );

        }
    }

    /**
     * @see parent::process_refund()
     * @param $order_id
     * @param $amount
     * @param $reason
     * @return bool
     */
    public function process_refund($order_id, $amount = null, $reason = ''): bool
    {
        try {
            return $this->refundCommand->execute($order_id, $amount);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * If we are on the pay-for-order page (e.g. MOTO) then load total from order instead of cart
     * @return string
     */
    private function getOrderTotal(): string
    {
        if (is_checkout_pay_page()) {
            global $wp;
            /** @var \WC_Order $order */
            $order = wc_get_order($wp->query_vars['order-pay']);
            return (string) $order->get_total();
        }
        return (string) WC()->cart->get_total('edit');
    }
}