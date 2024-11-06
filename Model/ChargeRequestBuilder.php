<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

use Shift4\Request\ChargeRequest;
use Shift4\WooCommerce\Model\ChargeRequestBuilder\BuilderInterface;

class ChargeRequestBuilder
{
    /** @var array|string[] Builder class names used to generate the charge request sent to Shift4 */
    private array $builderList = [
        \Shift4\WooCommerce\Model\ChargeRequestBuilder\BasicFields::class,
        \Shift4\WooCommerce\Model\ChargeRequestBuilder\Capture::class,
        \Shift4\WooCommerce\Model\ChargeRequestBuilder\ThreeDSecure::class,
        \Shift4\WooCommerce\Model\ChargeRequestBuilder\BillingAddress::class,
        \Shift4\WooCommerce\Model\ChargeRequestBuilder\ShippingAddress::class,
    ];

    /** @var array|null  */
    private ?array $builders = null;

    /**
     * Call each builder to do their own part and return a ChargeRequest
     * @param $order
     * @param $gateway
     * @return ChargeRequest
     */
    public function build($order, $gateway): ChargeRequest
    {
        $chargeRequest = new ChargeRequest();
        /** @var BuilderInterface $builder */
        foreach ($this->getBuilders() as $builder) {
            $builder->execute($chargeRequest, $order, $gateway);
        }
        return $chargeRequest;
    }

    /**
     * Lazy-load builders
     * Most requests these are not needed so only instantiate when needed
     * @return array
     */
    private function getBuilders(): array
    {
        if (is_array($this->builders)) {
            return $this->builders;
        }
        $this->builders = [];
        foreach ($this->builderList as $builderType) {
            $this->builders[] = new $builderType();
        }
        return $this->builders;
    }
}