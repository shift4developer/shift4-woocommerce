<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

class ConfigProvider
{
    private bool $initilised = false;
    private $publicKey;
    private $privateKey;
    private $debugMode;

    private const OPTION_KEY = 'woocommerce_shift4_shared_settings';
    public function getPublicKey(): ?string
    {
        $this->ensureInit();
        return $this->publicKey;
    }

    public function getPrivateKey(): ?string
    {
        $this->ensureInit();
        return $this->privateKey;
    }

    public function getDebugMode(): bool
    {
        $this->ensureInit();
        return $this->debugMode;
    }

    private function ensureInit()
    {
        if ($this->initilised) {
            return;
        }
        $settings = get_option(self::OPTION_KEY, null);
        $this->publicKey = $settings['shared_public_key'] ?? null;
        $this->privateKey = $settings['shared_secret_key'] ?? null;
        $this->debugMode = wc_string_to_bool($settings['debug_enabled'] ?? false);
        $this->initilised = true;
    }
}
