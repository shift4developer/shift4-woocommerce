<?php
defined('ABSPATH') or exit;
$formId = uniqid('applepayForm_');
/**
 * @var string $publicKey Shift4 API public key
 * @var string $orderTotal Order total for ApplePay sheet
 */
?>
<div id="payment-error" class="alert alert-danger" style="display: none"></div>
<input type="hidden" name="shift4_applepay_token" id="shift4_applepay_token"/>
<apple-pay-button id="apple-pay-submit-button" buttonstyle="black" type="buy" locale="en-US" class="w-full" lang="en-US"></apple-pay-button>
<script type="text/javascript">
    //<!--
    $ = jQuery;
    $(function () {
        const <?php echo esc_html($formId); ?> = $('form.woocommerce-checkout, #order_review, #add_payment_method');
        $(document.body).on('payment_method_selected', function(e) {
            const selected = document.querySelector('input[name="payment_method"]:checked').value;
            document.getElementById('place_order').style.visibility = (selected === 'shift4_applepay') ? 'hidden' : 'visible';
        });
        var shift4 = Shift4('<?php echo esc_html($publicKey); ?>');
        if (window.ApplePaySession && window.ApplePaySession.canMakePayments() && window.PaymentRequest) {
            $("#apple-pay-submit-button").click(payWithApplePay);
        } else {
            $("li.payment_method_shift4_applepay").hide();
        }

        function payWithApplePay() {
            // Configure PaymentRequest method details
            const applePayMethodData = {
                supportedMethods: 'https://apple.com/apple-pay',
                data: {
                    countryCode: 'GB',
                    supportedNetworks: [
                        'amex',
                        'discover',
                        'masterCard',
                        'visa',
                    ],
                },
            };
            // Configure details of shopping cart details
            const shoppingCartDetails = {
                total: {
                    label: '<?php echo esc_html(get_bloginfo('name')); ?>',
                    amount: {
                        currency: '<?php echo esc_html(get_woocommerce_currency()); ?>',
                        value: '<?php echo esc_html($orderTotal); ?>',
                    }
                },
            };

            // Create PaymentRequest using shift4.js
            const paymentRequest = shift4.createPaymentRequest([applePayMethodData], shoppingCartDetails);
            // Show payment sheet
            paymentRequest.show()
                .then(paymentResponse => createCharge(paymentResponse))
                .catch(err => displayError(err));
        }

        function createCharge(paymentResponse) {
            const request = paymentResponse.details.token.paymentData;
            document.getElementById('shift4_applepay_token').value = JSON.stringify(request);
            <?php echo esc_html($formId); ?>.submit()
            paymentResponse.complete('success')
        }
        function displayError(err) {
            const msg = typeof err === "string" ? err : err.message
            $('#payment-error').text(msg || 'Unknown error').show();
        }
    });
    // -->
</script>
