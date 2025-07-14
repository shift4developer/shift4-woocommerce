<?php
/*
 * Plugin Name: Shift4 for WooCommerce
 * Description: WooCommerce payments via the Shift4 platform
 * Version: 1.0.7
 * Plugin URI: https://dev.shift4.com/docs/plugins/woo-commerce/
 * Author: Shift4
 * Author URI: https://shift4.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: shift4-for-woocommerce
 * Requires PHP: 8.0
 * WC tested up to: 8.4.0
 */

defined('ABSPATH') or exit;

use Automattic\WooCommerce\Vendor\League\Container\Container;
use Automattic\WooCommerce\Vendor\League\Container\ReflectionContainer;
use Shift4\WooCommerce\Gateway\ApplePay;
use Shift4\WooCommerce\Gateway\Card;
use Shift4\WooCommerce\Model\ConfigProvider;

require_once plugin_dir_path(__FILE__) . '/utils/Shift4LogWC.php';

// Define version ID for current build
$commitHashFile = __DIR__ . '/buildId.php';
if (file_exists($commitHashFile)) {
    include $commitHashFile;
}

define("SHIFT4_PLUGIN_PATH", trailingslashit(plugin_dir_path(__FILE__)));

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

function getCardSingleton(Container $container)
{
    if (!defined('SHIFT4_CARD_REGISTERED')) {
        define('SHIFT4_CARD_REGISTERED', true);
        $card = $container->get(Card::class);
        $container->share(Card::class, function () use ($card) {
            return $card;
        });
    }
    return $container->get(Card::class);
}

function getApplePaySingleton(Container $container)
{
    if (!defined('SHIFT4_ApplePay_REGISTERED')) {
        define('SHIFT4_ApplePay_REGISTERED', true);
        $applePay = $container->get(ApplePay::class);
        $container->share(ApplePay::class, function () use ($applePay) {
            return $applePay;
        });
    }
    return $container->get(ApplePay::class);
}

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
            $methods[] = getCardSingleton($container);
            $methods[] = getApplePaySingleton($container);
            return $methods;
        });
        add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) use ($container) {
            if (!class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
                return;
            }
        
            require_once plugin_dir_path(__FILE__) . 'class-wc-shift4-block-support.php';
        
            $block_container = Automattic\WooCommerce\Blocks\Package::container();
            // registers as shared instance.
            $block_container->register(
                WC_Shift4_Block_Support::class,
                function () use ($container) {
                    return new WC_Shift4_Block_Support(getCardSingleton($container), getApplePaySingleton($container));
                }
            );
            $registry->register(
                $block_container->get(WC_Shift4_Block_Support::class)
            );
        });
        add_action( 'wp_footer', function() {
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
        });
        add_action('wp_head', function() {
            wp_enqueue_style(
                'shift4-css',
                plugins_url('/assets/css/shift4.css', __FILE__),
                false,
                SHIFT4_BUILD_HASH,
            );
        });
        add_action('wp_print_scripts', function () use ($container) { 
            $gateway = getApplePaySingleton($container);
            ?>
            
            <script>
                window.shift4ApplePaySettings = <?php echo wp_json_encode([
                    'applePayTitle' => $gateway->get_option( 'title' ),
                    'enabled' => $gateway->get_option( 'enabled' ) === 'yes',
                ]); ?>;
            </script>
            
            <?php
            
        });

        add_action('wp_ajax_shift4_log_js', 'shift4_log_js_callback');
        add_action('wp_ajax_nopriv_shift4_log_js', 'shift4_log_js_callback');
        function shift4_log_js_callback() {
            if (!empty($_POST['messages'])) {
                shift4_log($_POST['messages']);
            }

            wp_die();
        }
    });
}
