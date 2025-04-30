<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

use Shift4\Request\CardRequest;
use Shift4\Request\CustomerRequest;

class TokensiationManager
{
    public const SHIFT4_CUSTOMER_WP_USER_ID_KEY = 'shift4_customer_id';
    public function __construct(
        private GatewayFactory $gatewayFactory
    ) {}

    /**
     * @return int
     */
    public function saveCard()
    {
        /** @var \WP_User $user */
        $user = wp_get_current_user();
        $shift4CustomerId = $user->get(self::SHIFT4_CUSTOMER_WP_USER_ID_KEY);

        $gateway = $this->gatewayFactory->get();

        if (!$shift4CustomerId) {
            $customerRequest = new CustomerRequest();
            $customerRequest->email($user->user_email);
            $shift4Customer = $gateway->createCustomer($customerRequest);
            $shift4CustomerId = $shift4Customer->getId();
            $success = update_user_meta($user->ID, self::SHIFT4_CUSTOMER_WP_USER_ID_KEY, $shift4CustomerId);
        }

        $cardRequest = new CardRequest();
        $cardRequest->id($_POST['shift4_card_token']);
        $cardRequest->customerId($shift4CustomerId);
        $card = $gateway->createCard($cardRequest);
        $success = (int) $this->buildPaymentToken($card, $user);
        if ($success) {
            return $card->getId();
        }
        throw new \Exception('Unable to save token for some reason');
    }


    private function buildPaymentToken($card, $user)
    {
        $token = new \WC_Payment_Token_CC();
        $token->set_token($card->getId());
        $token->set_gateway_id('shift4_card');
        $token->set_card_type($card->getBrand());
        $token->set_last4($card->getLast4());
        $token->set_expiry_year($card->getExpYear());
        $token->set_expiry_month($card->getExpMonth());
        $token->set_user_id($user->ID);
        $token->add_meta_data('fingerprint', $card->getFingerprint(), true);
        return $token->save();
    }
}