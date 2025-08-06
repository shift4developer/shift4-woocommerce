<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model\ChargeRequestBuilder;

if (!defined('ABSPATH')) exit;

use Shift4\Request\AddressRequest;
use Shift4\Request\ChargeRequest;
use Shift4\Request\ShippingRequest;

class ShippingAddress implements BuilderInterface
{
    public function execute(ChargeRequest $chargeRequest, \WC_Order $order, \WC_Payment_Gateway $gateway = null): ChargeRequest
    {
        if (!$order->has_shipping_address()) {
            return $chargeRequest;
        }
        $shippingAddress = new ShippingRequest();
        $shippingAddress->name($order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name());
        $address = (new AddressRequest())
            ->line1($order->get_shipping_address_1())
            ->line2($order->get_shipping_address_2())
            ->city($order->get_shipping_city())
            ->state($order->get_shipping_state())
            ->zip($order->get_shipping_postcode())
            ->country($order->get_shipping_country());
        $shippingAddress->address($address);
        return $chargeRequest;
    }
}