<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

use Shift4\Request\CaptureRequest;

class CaptureFactory
{
    public function create(): CaptureRequest
    {
        return new CaptureRequest();
    }
}