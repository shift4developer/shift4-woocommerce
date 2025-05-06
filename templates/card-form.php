<?php defined('ABSPATH') or exit ?>
<div id="shift4-payment-form"
    data-amount="<?= $orderTotal ?>"
    data-currency="<?= get_woocommerce_currency() ?>">
    <div id="shift4-payment-error" class="woocommerce-NoticeGroup hidden">
        <div class="wc-block-components-notice-banner is-error" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
                <path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path>
            </svg>
            <div class="wc-block-components-notice-banner__content"></div>
        </div>
    </div>
    <div class="shift4-payment-field">
        <label class="shift4-payment-number-label">
            <?= __('Card number') ?>
        </label>
        <div class="shift4-payment-input shift4-payment-number-input" data-shift4="number"></div>
    </div>
    <div class="shift4-payment-fields">
        <div class="shift4-payment-field">
            <label class="shift4-payment-expiry-label">
                <?= __('Expiration') ?>
            </label>
            <div class="shift4-payment-input shift4-payment-expiry-input" data-shift4="expiry"></div>
        </div>
        <div class="shift4-payment-field">
            <label class="shift4-payment-cvv-label">
                <?= __('Cvv') ?>
            </label>
            <div class="shift4-payment-input shift4-payment-cvv-input" data-shift4="cvc"></div>
        </div>
    </div>
    <input type="hidden" name="shift4_card_token" id="shift4_card_token"/>
    <input type="hidden" name="shift4_card_fingerprint" id="shift4_card_fingerprint"/>
    <script>
        if (!window.shift4Initialised) {
            window.shift4Config = <?= json_encode($shift4Config) ?>;
            if (window.shift4JsLoaded) {
                initShift4();
                window.shift4Initialised = true;
            } else {
                document.addEventListener("shift4JsLoaded", function () {
                    initShift4();
                    window.shift4Initialised = true;
                });
            }
        }
    </script>
</div>
