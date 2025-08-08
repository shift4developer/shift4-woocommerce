<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway\Command;

if (!defined('ABSPATH')) exit;

use Shift4\Request\PaymentMethodRequest;
use Shift4\Request\PaymentMethodRequestApplePay;
use Shift4\Response\PaymentMethod;
use Shift4\WooCommerce\Model\GatewayFactory;

class PaymentMethodCommand
{
    public function __construct(
        private GatewayFactory $gatewayFactory,
    ) {}

    /**
     * @return PaymentMethod
     */
    public function execute(): PaymentMethod
    {
        $gateway = $this->gatewayFactory->get();
        $applePay = new PaymentMethodRequestApplePay();

        $applePay->token(json_decode(stripslashes($_POST['shift4_applepay_token'])));

        $paymentMethodRequest = new PaymentMethodRequest();
        $paymentMethodRequest->type('apple_pay');
        $paymentMethodRequest->applePay($applePay);
        return $gateway->createPaymentMethod($paymentMethodRequest);
    }
}