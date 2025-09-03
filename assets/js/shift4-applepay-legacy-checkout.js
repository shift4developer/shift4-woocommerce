$ = jQuery;
$(function () {
    const applepayForm_ddwe123412 = $('form.woocommerce-checkout, #order_review, #add_payment_method');
    $(document.body).on('payment_method_selected', function(e) {
        const selected = document.querySelector('input[name="payment_method"]:checked').value;
        document.getElementById('place_order').style.visibility = (selected === 'shift4_applepay') ? 'hidden' : 'visible';
    });
    var shift4 = Shift4(window.shift4Config.publicKey);
    if (window.ApplePaySession && window.ApplePaySession.canMakePayments() && window.PaymentRequest && window.shift4ApplePaySettings) {
        $(document.body).on('updated_checkout', function () {
            if ($('#apple-pay-submit-button').length) {
                $('#apple-pay-submit-button').off('click').on('click', function () {
                    payWithApplePay();
                });
            }
        });
    } else {
        const style = document.createElement('style');

        style.innerHTML = `
            li.payment_method_shift4_applepay {
                display: none !important;
            }
        `;
        document.head.appendChild(style);
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
                label: window.shift4Config.blogName,
                amount: {
                    currency: window.shift4ApplePaySettings.currency,
                    value: window.shift4ApplePaySettings.orderTotal,
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
        applepayForm_ddwe123412.submit()
        paymentResponse.complete('success')
    }
    function displayError(err) {
        const msg = typeof err === "string" ? err : err.message
        $('#payment-error').text(msg || 'Unknown error').show();
    }
});