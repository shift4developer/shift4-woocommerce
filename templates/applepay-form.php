<?php defined('ABSPATH') or exit; ?>
<div id="payment-error" class="alert alert-danger" style="display: none"></div>
<input type="hidden" name="<?php echo esc_attr(SHIFT4_APPLE_PAY_TOKEN); ?>" id="<?php echo esc_attr(SHIFT4_APPLE_PAY_TOKEN); ?>"/>
<apple-pay-button id="apple-pay-submit-button" buttonstyle="black" type="buy" locale="en-US" class="w-full" lang="en-US"></apple-pay-button>
