<?php declare(strict_types=1);

namespace Shift4\WooCommerce\Model;

class ThreeDSecureSource
{
    public const MODE_DISABLED = 'disabled';
    public const MODE_FRICTIONLESS = 'frictionless';
    public const MODE_STRICT = 'strict';

    public static function options(): array
    {
        return [
            self::MODE_DISABLED => __('Disabled', 'shift4'),
            self::MODE_FRICTIONLESS => __('Frictionless Mode', 'shift4'),
            self::MODE_STRICT => __('Strict Mode', 'shift4'),
        ];
    }

    public static function getDescription(): string
    {
        $disabled = __('Disabled', 'shift4');
        $disabledDescription = __('3DS is completely disabled', 'shift4');
        $frictionless = __('Frictionless', 'shift4');
        $frictionlessDescription = __('3DS will be used only if supported by the card', 'shift4');
        $strict = __('Strict', 'shift4');
        $strictDescription = __('Only 3DS cards will be permitted', 'shift4');

        $template = <<<HTML
<strong>%s:</strong> %s.<br/><br/>
<strong>%s:</strong> %s.<br/><br/>
<strong>%s:</strong> %s.
HTML;

        return sprintf(
            $template,
            $disabled,
            $disabledDescription,
            $frictionless,
            $frictionlessDescription,
            $strict,
            $strictDescription
        );

    }
}