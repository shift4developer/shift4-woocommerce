<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model\ChargeRequestBuilder;

if (!defined('ABSPATH')) exit;

use Shift4\Request\ChargeRequest;

interface BuilderInterface
{
    public function execute(ChargeRequest $chargeRequest, \WC_Order $order, \WC_Payment_Gateway $gateway = null): ChargeRequest;
}