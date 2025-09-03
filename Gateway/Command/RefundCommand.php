<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway\Command;

if (!defined('ABSPATH')) exit;

use Shift4\Request\RefundRequest;
use Shift4\WooCommerce\Model\CurrencyUnitConverter;
use Shift4\WooCommerce\Model\GatewayFactory;

class RefundCommand
{
    public function __construct(
        private GatewayFactory $gatewayFactory,
    ) {}

    public function execute($orderId, $amount): bool
    {
        $order = wc_get_order($orderId);
        $transactionId = $order->get_transaction_id();

        $refundRequest = new RefundRequest();
        $refundRequest->chargeId($transactionId);
        $refundRequest->amount(CurrencyUnitConverter::majorToMinor($amount));
        $gateway = $this->gatewayFactory->get();
        $response = $gateway->createRefund($refundRequest);
        // @TODO add logging
        $refundStatus = $response->getStatus();
        return ('successful' === $refundStatus);
    }
}