<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway;

use Shift4\WooCommerce\Model\CaptureStrategySource;
trait SharedGatewayConfigTrait
{
    private $sharedSettings;

    private $sharedFields = [
        'shared_public_key',
        'shared_secret_key',
        'capture_strategy',
        'debug_enabled',
        'action_when_fraud_detected'
    ];

    protected function initSharedFields()
    {
        add_action(
            'woocommerce_generate_shift4_config_section_html',
            [$this, 'generateSectionHtml'],
            10,
            3
        );

        add_filter(
            'pre_update_option_woocommerce_shift4_card_settings',
            [$this, 'stripSharedFields'],
            10,
            1
        );
        add_filter(
            'pre_update_option_woocommerce_shift4_applepay_settings',
            [$this, 'stripSharedFields'],
            10,
            1
        );

        $this->form_fields = array_merge($this->getSharedFields(), $this->init_form_fields());
    }

    public function stripSharedFields($values)
    {
        foreach ($this->sharedFields as $fieldToStrip) {
            if (array_key_exists($fieldToStrip, $values)) {
                unset($values[$fieldToStrip]);
            }
        }
        return $values;
    }

    protected function initSharedConfig()
    {
        $this->sharedSettings = get_option('woocommerce_shift4_shared_settings', null);

        // Init default values of shared settings
        $sharedFields = $this->getSharedFields();
        foreach ($sharedFields as $key => $value) {
            if (!isset($this->sharedSettings[$key]) && isset($value['default'])) {
                $this->sharedSettings[$key] = $value['default'];
            }
        }

        $this->sharedSettings['debug_enabled'] = wc_bool_to_string($this->sharedSettings['debug_enabled'] ?? false);
        $this->init_settings();
        $this->settings = array_merge($this->settings, $this->sharedSettings ?? []);
    }

    public function generateSectionHtml($value, $key, $field)
    {
        $text = $field['title'] ?? null;
        if (!$text) {
            return;
        }
        return '</table>
<h3 class="wc-settings-sub-title" style="text-decoration: underline">' . esc_html($text) . '</h3>
<table class="form-table">';
    }

    public function getSharedFields()
    {
        return [
            'shared_header' => [
                'type' => 'shift4_config_section',
                'title' => __('Shift4 Shared Configuration', 'shift4-for-woocommerce'),
            ],
            'shared_public_key' => [
                'title' => __('Public Key', 'shift4-for-woocommerce'),
                'type' => 'text',
            ],
            'shared_secret_key' => [
                'title' => __('Secret Key', 'shift4-for-woocommerce'),
                'type' => 'password',
            ],
            'capture_strategy' => [
                'title' => __('Capture Strategy', 'shift4-for-woocommerce'),
                'type' => 'select',
                'desc_tip' => __('Automatically capture or authorise only, capturing later', 'shift4-for-woocommerce'),
                'default' => CaptureStrategySource::MODE_CAPTURE,
                'options' => CaptureStrategySource::options(),
            ],
            'action_when_fraud_detected' => [
                'title' => __('Action with order when fraud is detected', 'shift4-for-woocommerce'),
                'type' => 'select',
                'desc_tip' => __('Choose the action to take for order when fraud is detected', 'shift4-for-woocommerce'),
                'default' => 'standard',
                'options' => [
                    'standard' => __('Standard', 'shift4-for-woocommerce'),
                    'cancel_order' => __('Cancel', 'shift4-for-woocommerce'),
                    'move_to_trash' => __('Move to trash', 'shift4-for-woocommerce'),
                ],
            ],
            'debug_enabled' => [
                'title' => __('Debug Logging', 'shift4-for-woocommerce'),
                'type' => 'checkbox',
                'label' => __('Enable Debug Mode', 'shift4-for-woocommerce'),
                'default' => 'no'
            ],
        ];
    }

    public function process_admin_options()
    {
        // First Process shared fields
        $postData = $this->get_post_data();
        $extractedValues = [];
        foreach ($this->sharedFields as $sharedField) {
            $key = $this->plugin_id . $this->id . '_' . $sharedField;
            if (array_key_exists($key, $postData)) {
                $extractedValues[$sharedField] = $postData[$key];
            }
        }
        update_option('woocommerce_shift4_shared_settings', $extractedValues);
        return parent::process_admin_options();
    }

    public function needs_setup()
    {
        return empty($this->settings['shared_public_key']) || empty($this->settings['shared_secret_key']);
    }
}
