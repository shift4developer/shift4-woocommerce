<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model\ChargeRequestBuilder;

use Shift4\Request\ChargeRequest;
use Shift4\WooCommerce\Model\CaptureStrategySource;

class Capture implements BuilderInterface
{
    public function execute(ChargeRequest $chargeRequest, \WC_Order $order, \WC_Payment_Gateway $gateway = null): ChargeRequest
    {
        if (isset($gateway->settings['capture_strategy']) &&
            $gateway->settings['capture_strategy'] === CaptureStrategySource::MODE_AUTH
        ) {
            $chargeRequest->captured(false);
        }
        return $chargeRequest;
    }
}