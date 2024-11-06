<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model\ChargeRequestBuilder;


use Shift4\Request\ChargeRequest;
use Shift4\WooCommerce\Model\CurrencyUnitConverter;

class BasicFields implements BuilderInterface
{
    public function execute(ChargeRequest $chargeRequest, \WC_Order $order, \WC_Payment_Gateway $gateway = null): ChargeRequest
    {
        $chargeRequest->amount(CurrencyUnitConverter::majorToMinor((string) $order->get_total()))
            ->currency(get_woocommerce_currency());
        return $chargeRequest;
    }
}