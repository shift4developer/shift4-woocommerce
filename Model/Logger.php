<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

class Logger extends \WC_Logger
{
    public function __construct(
        private ConfigProvider $configProvider,
        $handlers = null,
        $threshold = null,
    ) {
        parent::__construct($handlers, $threshold);
    }

    public const CONTEXT = [
        'source' => 'Shift4_Payments'
    ];

    /**
     * Override `debug` method to bypass logging of messages if plugin is not in debug mode
     *
     * @param $message
     * @param $context
     * @return void
     */
    public function debug($message, $context = array())
    {
        if (!$this->configProvider->getDebugMode()) {
            return;
        }
        parent::debug($message, $context);
    }

    /**
     * Overrride `log` function to merge in custom log filename
     *
     * @param $level
     * @param $message
     * @param $context
     * @return void
     */
    public function log($level, $message, $context = array())
    {
        $context = array_merge($context, self::CONTEXT);
        parent::log($level, $message, $context);
    }
}