<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

if (!defined('ABSPATH')) exit;

use Shift4\Request\CaptureRequest;

class CaptureFactory
{
    public function create(): CaptureRequest
    {
        return new CaptureRequest();
    }
}