<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model\ChargeRequestBuilder;

use Shift4\Request\AddressRequest;
use Shift4\Request\BillingRequest;
use Shift4\Request\ChargeRequest;


class BillingAddress implements BuilderInterface
{
    public function execute(ChargeRequest $chargeRequest, \WC_Order $order, \WC_Payment_Gateway $gateway = null): ChargeRequest
    {
        if ($gateway->id === 'shift4_applepay') {
            // ApplePay does not need a billing address
            return $chargeRequest;
        }

        if (!$order->has_billing_address()) {
            return $chargeRequest;
        }

        $billingAddress = (new BillingRequest())
            ->name($order->get_billing_first_name() . ' ' . $order->get_billing_last_name())
            ->email($order->get_billing_email());
        $address = (new AddressRequest())
            ->line1($order->get_billing_address_1())
            ->line2($order->get_billing_address_2())
            ->city($order->get_billing_city())
            ->state($order->get_billing_state())
            ->zip($order->get_billing_postcode())
            ->country($order->get_billing_country());
        $billingAddress->address($address);
        return $chargeRequest;
    }
}