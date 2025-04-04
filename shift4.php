<?php
/*
 * Plugin Name: Shift4
 * Description: WooCommerce payments via the Shift4 platform
 * Version: 1.0.2
 * Plugin URI: https://www.shift4.com/
 * Author: Gene Commerce
 * Text Domain: shift4
 * Requires PHP: 8.0
 * WC tested up to: 8.4.0
 */

defined('ABSPATH') or exit;

use Automattic\WooCommerce\Vendor\League\Container\Container;
use Automattic\WooCommerce\Vendor\League\Container\ReflectionContainer;
use Shift4\WooCommerce\Gateway\ApplePay;
use Shift4\WooCommerce\Gateway\Card;
use Shift4\WooCommerce\Model\ConfigProvider;

// Define version ID for current build
$commitHashFile = __DIR__ . '/buildId.php';
if (file_exists($commitHashFile)) {
    include $commitHashFile;
}

define("SHIFT4_PLUGIN_PATH", trailingslashit(plugin_dir_path(__FILE__)));

// Test to see if WooCommerce is active (including network activated).
$plugin_path = trailingslashit( WP_PLUGIN_DIR ) . 'woocommerce/woocommerce.php';
if (in_array($plugin_path, wp_get_active_and_valid_plugins())) {
    require_once __DIR__ . '/vendor/autoload.php';
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
            $methods[] = $container->get(Card::class);
            $methods[] = $container->get(ApplePay::class);
            return $methods;
        });
        add_action( 'wp_footer', function() {
            wp_enqueue_script(
                'shift4-js-client',
                'https://js.dev.shift4.com/shift4.js',
            );
            wp_enqueue_script(
                'shift4-js',
                plugins_url('/assets/js/shift4.js', __FILE__),
                ['jquery'],
                SHIFT4_BUILD_HASH,
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
}
