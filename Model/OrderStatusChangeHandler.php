<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

class OrderStatusChangeHandler
{
    private $observedNewStatuses = [
        'processing',
        'completed',
    ];
    /**
     * Register `woocommerce_order_status_changed` action handler
     */
    public function __construct(
        private GatewayFactory $gatewayFactory,
        private CaptureFactory $captureFactory,
    ) {
        add_action('woocommerce_order_status_changed', [$this, 'handle'], 10, 3);
    }

    public function handle($id, $oldStatus, $newStatus)
    {
        // Are we interested in the status change
        if (!in_array($newStatus, $this->observedNewStatuses)) {
            return;
        }

        // Is the gateway ours
        $order = wc_get_order($id);
        $method = $order->get_payment_method();
        if (!str_starts_with($method, 'shift4_')) {
            return;
        }

        $this->capture($order);
    }

    private function capture(\WC_Order $order)
    {
        $captureRequest = $this->captureFactory->create()
            ->chargeId($order->get_transaction_id());
        try {
            $gateway = $this->gatewayFactory->get();
            $response = $gateway->captureCharge($captureRequest);
            $order->add_order_note(
                sprintf(
                    'Successfully captured %s %s',
                    $response->getCurrency(),
                    CurrencyUnitConverter::minorToMajor((int) $response->getAmount())
                )
            );
        } catch (\Exception $e) {
            $order->add_order_note(
                sprintf(
                    'Attempted capture failed: %s',
                    $e->getMessage(),
                )
            );
        }
    }
}