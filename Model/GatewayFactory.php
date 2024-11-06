<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

use Shift4\Shift4Gateway;

class GatewayFactory
{
    public const USER_AGENT = 'Shift4 for WooCommerce';
    private $instance;
    public function __construct(
        private ConfigProvider $gatewaySettings
    ) {}

    public function get(): Shift4Gateway
    {
        if ($this->instance) {
            return $this->instance;
        }
        $this->instance = new Shift4Gateway($this->gatewaySettings->getPrivateKey());
        $this->instance->setUserAgent(self::USER_AGENT);
        return $this->instance;
    }
}