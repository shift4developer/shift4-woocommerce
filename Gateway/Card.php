<?php

declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway;

if (!defined('ABSPATH')) exit;

use Shift4\Exception\Shift4Exception;
use Shift4\WooCommerce\Gateway\Command\ChargeCommand;
use Shift4\WooCommerce\Gateway\Command\RefundCommand;
use Shift4\WooCommerce\Model\ChargeRequestBuilder;
use Shift4\WooCommerce\Model\CurrencyUnitConverter;
use Shift4\WooCommerce\Model\ThreeDSecureSource;
use Shift4\WooCommerce\Model\Logger;
use Shift4\WooCommerce\Model\TokensiationManager;
use Shift4\WooCommerce\Gateway\FraudHandler;

class Card extends \WC_Payment_Gateway_CC
{
    use SharedGatewayConfigTrait;
    use SharedGatewayLogic;

    public const ID = 'shift4_card';

    public const PAYMENT_TYPE_NEW_CARD = 'new_card';
    public const PAYMENT_TYPE_SAVE_CARD = 'save_card';
    public const PAYMENT_TYPE_EXISTING_TOKEN = 'existing_token';

    public function __construct(
        private ChargeRequestBuilder $chargeRequestBuilder,
        private ChargeCommand $chargeCommand,
        private RefundCommand $refundCommand,
        private TokensiationManager $tokensiation,
        private Logger $logger,
    ) {
        $this->id = self::ID;
        $this->icon = 'https://www.shift4shop.com/images/credit-card-logos/cc-sm-4_b.png';
        $this->has_fields = true;
        $this->method_title = 'Shift4 (Card)';
        $this->method_description = 'Take card payments via Shift4';
        $this->view_transaction_url = 'https://dev.shift4.com/charges/%s';
        $this->supports = [
            'products',
            'refunds',
            'tokenization',
        ];

        // Init fields and config, inc. shared credentials
        $this->initSharedFields();
        $this->initSharedConfig();
        $this->title = $this->get_option('title');

        // If merchant does not want tokenization disable this feature
        if (!wc_string_to_bool($this->settings['saved_cards_enabled'])) {
            if (($key = array_search('tokenization', $this->supports)) !== false) {
                unset($this->supports[$key]);
            }
        }

        // Register action to save payment settings
        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
        // Register hook to add payment card network icons to saved card entries
        add_filter('woocommerce_payment_gateway_get_saved_payment_method_option_html', [$this, 'addCardNetworkIconToSavedCards'], 10, 2);

        // Register hook. If merchant does not want tokenization, filter the saved cards for checkout block page.
        add_filter('woocommerce_saved_payment_methods_list', function ($methods) {
            if (!wc_string_to_bool($this->settings['saved_cards_enabled'])) {
                // Filter all the payment methods ('cc' is the real target)
                foreach ($methods as $type => $tokens) {
                    // Filter all tokenization which method.gateway is shift4_card
                    $methods[$type] = array_filter($tokens, function ($token) {
                        return isset($token['method']['gateway']) && $token['method']['gateway'] !== self::ID;
                    });
                    // Remove the payment method if it's empty
                    if (empty($methods[$type])) {
                        unset($methods[$type]);
                    }
                }
            }
            return $methods;
        }, 10, 2);
    }

    // These are options you’ll show in admin on your gateway settings page and make use of the WC Settings API.
    public function init_form_fields()
    {
        return [
            'method_header' => [
                'type' => 'shift4_config_section',
                'title' => __('Shift4 Card Payments Configuration', 'shift4-for-woocommerce'),
            ],
            'enabled' => [
                'title' => __('Enable/Disable', 'shift4-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Card Payments', 'shift4-for-woocommerce'),
                'default' => 'no'
            ],
            'title' => [
                'title' => __('Title', 'shift4-for-woocommerce'),
                'type' => 'text',
                'description' => __('This controls the title which the user sees during checkout.', 'shift4-for-woocommerce'),
                'default' => __('Card Payment', 'shift4-for-woocommerce'),
                'desc_tip' => true,
            ],
            '3ds_mode' => [
                'title' => __('3DS Mode', 'shift4-for-woocommerce'),
                'type' => 'select',
                'description' => ThreeDSecureSource::getDescription(),
                'desc_tip' => true,
                'default' => ThreeDSecureSource::MODE_FRICTIONLESS,
                'options' => ThreeDSecureSource::options(),
            ],
            'saved_cards_enabled' => [
                'title' => __('Card Vaulting', 'shift4-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Saved Cards', 'shift4-for-woocommerce'),
                'default' => 'yes'
            ],
        ];
    }

    public function form()
    {
        $shift4Config = [
            'threeDS' => $this->threeDSecureMode(),
            'threeDSValidationMessage' =>  __('3DS validation failed.', 'shift4-for-woocommerce'),
            'publicKey' => $this->getPublicKey(),
            'componentNeedsTriggering' => is_checkout_pay_page() || is_add_payment_method_page(),
        ];
        wc_get_template(
            'card-form.php',
            [
                'orderTotal' => $this->getOrderTotal(),
                'shift4Config' => $shift4Config,
            ],
            'shift4',
            SHIFT4_PLUGIN_PATH . 'templates/',
        );
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function validate_fields()
    {
        if (isset($_POST['wc-shift4_card-payment-token']) && 'new' !== $_POST['wc-shift4_card-payment-token']) {
            return true;
        }
        if (empty($_POST['shift4_card_token'])) {
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

        $chargeRequest = $this->chargeRequestBuilder->build($order, $this);

        switch ($this->determinePaymentRequestType()) {
            case self::PAYMENT_TYPE_EXISTING_TOKEN:
                $vaultedToken = \WC_Payment_Tokens::get($_POST['wc-shift4_card-payment-token']);
                if ($vaultedToken->get_user_id() !== get_current_user_id()) {
                    throw new \Exception('Not your token');
                }
                $token = $vaultedToken->get_token();
                $user = wp_get_current_user();
                $shift4CustomerId = $user->get(TokensiationManager::SHIFT4_CUSTOMER_WP_USER_ID_KEY);
                $chargeRequest->customerId($shift4CustomerId);
                break;
            case self::PAYMENT_TYPE_SAVE_CARD:
                $token = $this->tokensiation->saveCard();
                $user = wp_get_current_user();
                $shift4CustomerId = $user->get(TokensiationManager::SHIFT4_CUSTOMER_WP_USER_ID_KEY);
                $chargeRequest->customerId($shift4CustomerId);
                break;
            default:
                $token = $_POST['shift4_card_token'];
                break;
        }
        $chargeRequest->card($token);

        try {
            $charge = $this->chargeCommand->execute($chargeRequest);
            return $this->handleChargeResponse($order, $charge);
        } catch (Shift4Exception $e) {
            $action = $this->settings['action_when_fraud_detected'];
            FraudHandler::handleFraud($e, $order, $action);
            $this->logger->error(
                sprintf(
                    'Encountered `Shift4Exception` while creating charge for %s. Request: %s, Error: %s',
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
     * @return string[]
     */
    public function add_payment_method()
    {
        try {
            if ($this->tokensiation->saveCard()) {
                return [
                    'result' => 'success',
                ];
            }
            return [
                'result' => 'failure',
            ];
        } catch (\Exception) {
            return [
                'result' => 'failure',
            ];
        }
    }

    private function determinePaymentRequestType()
    {
        $shift4_card_fingerprint = null;
        if (isset($_POST['shift4_card_fingerprint'])) {
            $shift4_card_fingerprint = sanitize_text_field($_POST['shift4_card_fingerprint']);
        }

        $user_saved_tokens = \WC_Payment_Tokens::get_customer_tokens(get_current_user_id(), 'shift4_card');

        foreach ($user_saved_tokens as $token) {
            $fingerprint = $token->get_meta('fingerprint');
            if ($fingerprint && $fingerprint === $shift4_card_fingerprint) {
                $_POST['wc-shift4_card-payment-token'] = $token->get_id();
                break;
            }
        }

        /**
         * use existing token:
         * - wc-shift4_card-payment-token=8
         *
         * place order and save card:
         * - wc-shift4_card-payment-token=new
         * - shift4_card_token=sometoken
         * - wc-shift4_card-new-payment-method=true
         *
         * use card and dont save:
         * wc-shift4_card-payment-token=new
         * - shift4_card_token=sometoken
         */
        // Use saved token scenario
        if (isset($_POST['wc-shift4_card-payment-token']) && is_numeric($_POST['wc-shift4_card-payment-token'])) {
            return self::PAYMENT_TYPE_EXISTING_TOKEN;
        }

        // Place order and save card scenario
        if (
            isset($_POST['wc-shift4_card-new-payment-method'])
            && (
                $_POST['wc-shift4_card-new-payment-method'] === 'true'
                || ($_POST['wc-shift4_card-new-payment-method'] === '1')
            )
        ) {
            return self::PAYMENT_TYPE_SAVE_CARD;
        }

        // Other scenarios didn't match so assume new card but don't save
        return self::PAYMENT_TYPE_NEW_CARD;
    }

    /**
     * If we are on the pay-for-order page (e.g. MOTO) then load total from order instead of cart
     * @return int
     */
    private function getOrderTotal(): int
    {
        if (is_checkout_pay_page()) {
            global $wp;
            /** @var \WC_Order $order */
            $order = wc_get_order($wp->query_vars['order-pay']);
            return CurrencyUnitConverter::majorToMinor((string) $order->get_total());
        }
        if (is_add_payment_method_page()) {
            return 0;
        }
        return CurrencyUnitConverter::majorToMinor((string) WC()->cart->get_total('edit'));
    }

    public function addCardNetworkIconToSavedCards($html, $token)
    {
        $iconUrl = match ($token->get_card_type()) {
            'Visa' => 'https://js.securionpay.com/6ab079a7/v2/img/visa.svg',
            'Maestro',
            'MasterCard' => 'https://js.securionpay.com/6ab079a7/v2/img/mastercard.svg',
            'American Express' => 'https://js.securionpay.com/6ab079a7/v2/img/amex.svg',
            'Discover' => 'https://js.securionpay.com/6ab079a7/v2/img/discover.svg',
            'Diners Club' => 'https://js.securionpay.com/6ab079a7/v2/img/diners.svg',
            'JCB' => 'https://js.securionpay.com/6ab079a7/v2/img/jcb.svg',
            default => 'https://js.securionpay.com/6ab079a7/v2/img/unknown.svg',
        };

        if (!preg_match('/([\S\s]*<label for="wc-shift4_card-payment-token-\d+">)([\S\s]*)/', $html, $matches)) {
            return $html;
        }
        return sprintf(
            // phpcs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage
            '%s<img src="%s" width="28px" height="18px" alt="%s"> %s',
            $matches[1],
            esc_url($iconUrl),
            esc_attr($token->get_card_type() . ' icon'),
            $matches[2],
        );
    }

    public function threeDSecureMode(): string
    {
        return $this->settings['3ds_mode'];
    }

    /**
     * This shouldn't be needed but WooCommerce shows the "Save to account" button on MOTO orders when user is a guest
     * @param $feature
     * @return bool
     */
    public function supports($feature)
    {
        if ($feature === 'tokenization' && !is_user_logged_in()) {
            return false;
        }
        return parent::supports($feature);
    }
}
