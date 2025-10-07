<?php
/*
 * Plugin Name: Shift4 for WooCommerce
 * Requires Plugins: woocommerce
 * Description: WooCommerce payments via the Shift4 platform
 * Version: 1.0.10
 * Plugin URI: https://dev.shift4.com/docs/plugins/woo-commerce/
 * Author: Shift4
 * Author URI: https://shift4.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shift4-for-woocommerce
 * Requires PHP: 8.0
 * Requires at least: 6.7
 * WC tested up to: 10.0.2
 */

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/vendor/autoload.php';

use League\Container\Container;
use League\Container\ReflectionContainer;
use Shift4\WooCommerce\Gateway\ApplePay;
use Shift4\WooCommerce\Gateway\Card;
use Shift4\WooCommerce\Model\ConfigProvider;

// Define version ID for current build
$commitHashFile = __DIR__ . '/buildId.php';
if (file_exists($commitHashFile)) {
    include $commitHashFile;
}

$constsFile = __DIR__ . '/Utils/consts.php';
if (file_exists($constsFile)) {
    include $constsFile;
}

define("SHIFT4_PLUGIN_PATH", plugin_dir_path(__FILE__));
define("SHIFT4_PLUGIN_URL", plugin_dir_url(__FILE__));

// Declare the plugin is compatible with HPOS(High-Performance Order Storage)
add_action('before_woocommerce_init', function () {
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables', // The internal flag of HPOS
            __FILE__,
            true
        );
    }
});

function shift4_get_card_singleton(Container $container)
{
    if (!defined('SHIFT4_CARD_REGISTERED')) {
        define('SHIFT4_CARD_REGISTERED', true);
        $card = $container->get(Card::class);
        $container->add(Card::class, function () use ($card) {
            return $card;
        });
    }
    return $container->get(Card::class);
}

function shift4_get_apple_pay_singleton(Container $container)
{
    if (!defined('SHIFT4_ApplePay_REGISTERED')) {
        define('SHIFT4_ApplePay_REGISTERED', true);
        $applePay = $container->get(ApplePay::class);
        $container->add(ApplePay::class, function () use ($applePay) {
            return $applePay;
        });
    }
    return $container->get(ApplePay::class);
}

add_action('admin_init', function () {
    $settings = get_option(SHIFT4_SHARED_SETTINGS_OPTION_KEY, null);
    if (!$settings) {
        $settings = get_option(SHIFT4_SHARED_SETTINGS_OPTION_KEY_PREVIOUS, null);
        if ($settings) {
            update_option(SHIFT4_SHARED_SETTINGS_OPTION_KEY, $settings);
            delete_option(SHIFT4_SHARED_SETTINGS_OPTION_KEY_PREVIOUS);
        }
    }
});

add_action('plugins_loaded', function() {
    // Init DI container and auto-wiring
    $container = new Container();
    $container->delegate(new ReflectionContainer());

    // Define class configurations
    $container->add(ConfigProvider::class, ConfigProvider::class, true);

    // Init other classes so they can register themselves as needed
    $container->get(\Shift4\WooCommerce\Model\OrderStatusChangeHandler::class);
    $container->get(\Shift4\WooCommerce\Gateway\Command\DeleteCardCommand::class);

    add_filter('woocommerce_payment_gateways', function ($methods) use ($container) {
        // Load in the Gateway instance
        $methods[] = shift4_get_card_singleton($container);
        $methods[] = shift4_get_apple_pay_singleton($container);
        return $methods;
    });
    add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) use ($container) {
        if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
            return;
        }
    
        require_once SHIFT4_PLUGIN_PATH . 'class-shift4-wc-block-support.php';
    
        $block_container = Automattic\WooCommerce\Blocks\Package::container();
        // registers as shared instance.
        $block_container->register(
            Shift4_WC_Block_Support::class,
            function () use ($container) {
                return new Shift4_WC_Block_Support(shift4_get_card_singleton($container), shift4_get_apple_pay_singleton($container));
            }
        );
        $registry->register(
            $block_container->get(Shift4_WC_Block_Support::class)
        );
    });
    add_action( 'wp_footer', function() use ($container) {
        wp_enqueue_script(
            'shift4-js-client',
            'https://js.dev.shift4.com/shift4.js',
            [],
            SHIFT4_BUILD_HASH,
            false
        );
        wp_enqueue_script(
            'shift4-js',
            plugins_url('/assets/js/shift4.js', __FILE__),
            ['jquery'],
            SHIFT4_BUILD_HASH,
            false
        );

        $gatewayCard = shift4_get_card_singleton($container);

        $shift4CardViewSettings = [
            'threeDS' => $gatewayCard->threeDSecureMode(),
            'threeDSValidationMessage' =>  __('3DS validation failed.', 'shift4-for-woocommerce'),
            'componentNeedsTriggering' => is_checkout_pay_page() || is_add_payment_method_page(),
        ];

        wp_localize_script(
            'wc-shift4-blocks-integration',
            'shift4CardViewSettings',
            $shift4CardViewSettings
        );

        $gatewayApplePay = shift4_get_apple_pay_singleton($container);

        $shift4ApplePaySettings = [
            'applePayTitle' => $gatewayApplePay->get_option( 'title' ),
            'enabled' => $gatewayApplePay->get_option( 'enabled' ) === 'yes',
            'currency' => get_woocommerce_currency(),
            'orderTotal' => WC()->cart ? WC()->cart->get_total('edit') : ''
        ];

        wp_localize_script(
            'wc-shift4-blocks-integration',
            'shift4ApplePaySettings',
            $shift4ApplePaySettings
        );

        wp_enqueue_script(
            'shift4-card-legacy-checkout',
            plugins_url('/assets/js/shift4-card-legacy-checkout.js', __FILE__),
            ['jquery'],
            SHIFT4_BUILD_HASH,
            false
        );

        wp_enqueue_script(
            'shift4-applepay-legacy-checkout',
            plugins_url('/assets/js/shift4-applepay-legacy-checkout.js', __FILE__),
            ['jquery'],
            SHIFT4_BUILD_HASH,
            false
        );
    });
    add_action('wp_head', function() {
        wp_enqueue_style(
            'shift4-css',
            plugins_url('/assets/css/shift4.css', __FILE__),
            false,
            SHIFT4_BUILD_HASH,
        );
    });
});

