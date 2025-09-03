<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model\ChargeRequestBuilder;

if (!defined('ABSPATH')) exit;

use Shift4\Request\ChargeRequest;
use Shift4\Request\ThreeDSecureRequest;
use Shift4\WooCommerce\Model\ThreeDSecureSource;

class ThreeDSecure implements BuilderInterface
{
    public function execute(ChargeRequest $chargeRequest, \WC_Order $order, \WC_Payment_Gateway $gateway = null): ChargeRequest
    {
        // Only need to set 3DS config if payment method has appropriate configuration
        if (array_key_exists('3ds_mode', $gateway->settings)) {
            $threeDSecure = new ThreeDSecureRequest();
            switch ($gateway->settings['3ds_mode']) {
                case ThreeDSecureSource::MODE_DISABLED:
                    $threeDSecure->requireEnrolledCard(false);
                    $threeDSecure->requireSuccessfulLiabilityShiftForEnrolledCard(false);
                    break;

                case ThreeDSecureSource::MODE_STRICT:
                    $threeDSecure->requireEnrolledCard(true);
                    $threeDSecure->requireSuccessfulLiabilityShiftForEnrolledCard(true);
                    break;

                default: // Default to "Frictionless" mode
                    $threeDSecure->requireEnrolledCard(false);
                    $threeDSecure->requireSuccessfulLiabilityShiftForEnrolledCard(true);

            }
            $chargeRequest->threeDSecure($threeDSecure);
        }
        return $chargeRequest;
    }
}