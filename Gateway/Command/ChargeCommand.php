<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway\Command;

if (!defined('ABSPATH')) exit;

use Shift4\Request\RefundRequest;
use Shift4\WooCommerce\Model\GatewayFactory;

class ChargeCommand
{
    public function __construct(
        private GatewayFactory $gatewayFactory,
    ) {}

    public function execute($charge)
    {
        $gateway = $this->gatewayFactory->get();
        return $gateway->createCharge($charge);
    }
}