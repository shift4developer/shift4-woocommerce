function initShift4() {
    if (!window.shift4Config) {
        console.error('Shift4 payment gateway not configured');
        return;
    }

    const $ = jQuery;
    const $checkoutForm = $('form.woocommerce-checkout, #order_review, #add_payment_method');
    const shift4 = window.Shift4(window.shift4Config.publicKey);
    let components;

    $('body').on('updated_checkout', function () {
        // Create components to securely collect sensitive payment data
        try {
            components = shift4.createComponentGroup().automount("#shift4-payment-form");
        } catch (err) {
            // When WC checkout initializes it reloads the payment section so catch any missing DOM errors
        }
    });

    // Listen for Shift4 post-messages to automatically select "new" option when user clicks the card form
    window.addEventListener('message', function(event) {
        if (event.origin === 'https://js.dev.shift4.com') {
            $(':input.woocommerce-SavedPaymentMethods-tokenInput').trigger('click');
        }
    });

    // Trigger one time as the order-pay screen and add-to-account does not trigger it, and we use for initialization
    if (window.shift4Config.componentNeedsTriggering) {
        $(document.body).trigger('updated_checkout');
    }

    // Handler for checkout
    $checkoutForm.off('.shift4').on('checkout_place_order_shift4_card.shift4', function () {
        // If payment is stored card no need to tokenize
        const newCardMethod = document.getElementById('wc-shift4_card-payment-token-new');
        if (newCardMethod && newCardMethod.checked === false) {
            return;
        }
        if (!$checkoutForm.attr('shift4-validated') || $checkoutForm.attr('shift4-validated') === 'false') {
            clearError();
            paymentFormSubmit();
            return false;
        } else {
            setValidationState(false);
        }
    });

    // Handler for add-payment-method and order-review form
    $checkoutForm.on('submit', function (event) {
        const currentForm = $checkoutForm[0];
        const forms = ['add_payment_method', 'order_review'];
        if (forms.includes(currentForm.id) && document.getElementById('payment_method_shift4_card').checked) {
            const newCardToken = document.getElementById('wc-shift4_card-payment-token-new');
            // Payment is using stored card so just submit form normally, no tokenization required
            if (newCardToken && newCardToken.checked === false) {
                return;
            }
            if (!$checkoutForm.attr('shift4-validated') || $checkoutForm.attr('shift4-validated') === 'false') {
                event.preventDefault();
                clearError();
                paymentFormSubmit();
            } else {
                setValidationState(false);
            }
        }
    });

    function paymentFormSubmit() {
        // Send card data to Shift
        return shift4.createToken(components)
            .then(tokenCreatedCallback)
            .catch(errorCallback);
    }

    function tokenCreatedCallback(token) {
        if (['strict', 'frictionless'].includes(window.shift4Config.threeDS)) {
            const $shift4Form = $('#shift4-payment-form');
            var request = {
                amount: $shift4Form.data('amount'),
                currency: $shift4Form.data('currency'),
                card: token.id,
            };
            // Open frame with 3-D Secure process
            shift4.verifyThreeDSecure(request)
                .then(threeDSecureCompletedCallback)
                .catch(errorCallback);
        } else {
            setTokenAndContinue(token);
        }
    }

    function errorCallback(error) {
        if (error.message) {
            // Display error message
            addError(error.message);
        }
        setValidationState(false);
        $('form#order_review').unblock();
        $('form#add_payment_method').unblock();
        return false;
    }

    function threeDSecureCompletedCallback(token) {
        switch (window.shift4Config.threeDS) {
            case 'disabled':
                setTokenAndContinue(token);
                break;

            case 'frictionless':
                if (token.threeDSecureInfo?.enrolled === false || token.threeDSecureInfo?.liabilityShift === 'successful') {
                    setTokenAndContinue(token);
                } else {
                    addError(window.shift4Config.threeDSValidationMessage);
                }
                break;

            case 'strict':
                if (token.threeDSecureInfo?.enrolled === true) {
                    setTokenAndContinue(token);
                } else {
                    addError(window.shift4Config.threeDSValidationMessage);
                }
        }
    }

    function setTokenAndContinue(token) {
        document.getElementById('shift4_card_token').value = token.id;
        setValidationState(true);
        $checkoutForm.submit();
    }

    function setValidationState(state) {
        $checkoutForm.attr('shift4-validated', state);
    }

    function addError(errorMessage) {
        const $errorMessage = $('#shift4-payment-error');
        $errorMessage.removeClass('hidden');
        $errorMessage.find('.wc-block-components-notice-banner__content').text(errorMessage);
        $('form#order_review').unblock();
        $('form#add_payment_method').unblock();
    }

    function clearError() {
        const $errorMessage = $('#shift4-payment-error');
        $errorMessage.addClass('hidden');
    }
}
const event = new Event("shift4JsLoaded");
document.dispatchEvent(event);
window.shift4JsLoaded = true;
