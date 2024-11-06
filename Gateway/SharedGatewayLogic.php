<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway;

use Shift4\WooCommerce\Model\CurrencyUnitConverter;

trait SharedGatewayLogic
{
    protected function handleChargeResponse($order, $charge): array
    {
        $chargeId = $charge->getId();
        if ($charge->getCaptured()) {
            $order->payment_complete($chargeId);
        } else {
            $order->set_transaction_id($chargeId);
            $amount = $charge->getCurrency() . CurrencyUnitConverter::minorToMajor((int) $charge->getAmount());
            $order->update_status(
                'wc-on-hold',
                "Authorised a charge of $amount. Un-hold order to capture."
            );
        }
        return [
            'result' => 'success',
            'redirect' => $this->get_return_url($order),
        ];
    }

    public function getPublicKey(): string
    {
        return $this->settings['shared_public_key'] ?? '';
    }
}
