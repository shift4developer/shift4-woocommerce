<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Gateway\Command;

if (!defined('ABSPATH')) exit;

use Shift4\WooCommerce\Model\GatewayFactory;
use Shift4\WooCommerce\Model\Logger;

class DeleteCardCommand
{
    public function __construct(
        private GatewayFactory $gatewayFactory,
        private Logger $logger,
    ) {
        add_action('woocommerce_payment_token_deleted', [$this, 'execute'], 10, 2);
    }

    public function execute($id, $token): void
    {
        // Only process deletions for our tokens
        if ($token->get_gateway_id() !== \Shift4\WooCommerce\Gateway\Card::ID) {
            return;
        }

        try {
            $this->logger->debug(
                sprintf(
                'Deleting stored card token:%s for user with ID: %s',
                    $token->get_token(),
                    $token->get_user_id(),
                )
            );

            $gateway = $this->gatewayFactory->get();

            $shift4Id = get_user_meta(
                $token->get_user_id(),
                \Shift4\WooCommerce\Model\TokensiationManager::SHIFT4_CUSTOMER_WP_USER_ID_KEY,
                true,
            );

            $this->logger->debug(
                sprintf(
                    'WooCommerce customer ID:%s resolves to Shift4 customer ID:%s',
                    $shift4Id,
                    $token->get_user_id(),
                )
            );

            $gateway->deleteCard(
                $shift4Id,
                $token->get_token(),
            );
        } catch (\Exception $e) {
            $this->logger->error(
                sprintf(
                    'Exception encountered while deleting customer card: %s',
                    $e->getMessage()
                )
            );
        }
    }
}