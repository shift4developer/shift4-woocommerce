const errorComponent = `
    <div id="shift4-payment-error" class="woocommerce-NoticeGroup hidden">
        <div class="wc-block-components-notice-banner is-error" role="alert">
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24" aria-hidden="true" focusable="false">
                <path d="M12 3.2c-4.8 0-8.8 3.9-8.8 8.8 0 4.8 3.9 8.8 8.8 8.8 4.8 0 8.8-3.9 8.8-8.8 0-4.8-4-8.8-8.8-8.8zm0 16c-4 0-7.2-3.3-7.2-7.2C4.8 8 8 4.8 12 4.8s7.2 3.3 7.2 7.2c0 4-3.2 7.2-7.2 7.2zM11 17h2v-6h-2v6zm0-8h2V7h-2v2z"></path>
            </svg>
            <div class="wc-block-components-notice-banner__content"></div>
        </div>
    </div>
`;
const shift4FormSelector = '#shift4-payment-form';
const shift4ErrorSelector = '#shift4-payment-error';

function initShift4(blockOptions) {

    if (!window.shift4Config) {
        console.error('Shift4 payment gateway not configured');
        return;
    }

    const $ = jQuery;
    const $checkoutForm = $('form.woocommerce-checkout, #order_review, #add_payment_method');
    const shift4 = window.Shift4(window.shift4Config.publicKey);
    let components;

    function updatedCheckout() {
        // Create components to securely collect sensitive payment data
        try {
            const isInitialzed = $('[data-shift4="number"]').children().size() > 0;

            if (!isInitialzed) {
                components = shift4.createComponentGroup().automount(shift4FormSelector);
            }
        } catch (err) {
            // When WC checkout initializes it reloads the payment section so catch any missing DOM errors
        }
    }
    window.shift4UpdatedCheckout = updatedCheckout

    $('body').on('updated_checkout', function () {
        updatedCheckout()
    });

    // Listen for Shift4 post-messages to automatically select "new" option when user clicks the card form
    window.addEventListener('message', function (event) {
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
            const $shift4Form = $(shift4FormSelector);
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
        $(document.body).trigger('checkout_error');
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
        if (blockOptions) {
            blockOptions.paymentMethodDataRef.current = {
                'shift4_card_token': token.id
            }
        }
        document.getElementById('shift4_card_token').value = token.id;
        setValidationState(true);
        $checkoutForm.submit();
    }

    function setValidationState(state) {
        $checkoutForm.attr('shift4-validated', state);
    }

    function createErrorElement() {
        if (!$(shift4ErrorSelector).length) {
            const errorElement = $(errorComponent);
            $(shift4FormSelector).prepend(errorElement);
        }
    }

    function addError(errorMessage) {
        createErrorElement();
        const $errorMessage = $(shift4ErrorSelector);
        $errorMessage.removeClass('hidden');
        $errorMessage.find('.wc-block-components-notice-banner__content').text(errorMessage);
        $('form#order_review').unblock();
        $('form#add_payment_method').unblock();
    }

    function clearError() {
        createErrorElement();
        const $errorMessage = $(shift4ErrorSelector);
        $errorMessage.addClass('hidden');
    }

    /**
     * paymentFormSubmit edition for checkout block
     * 
     * @param {Object} params params amount number and currency code return by woocommerce in Shift4PaymentForm
     */
    async function block_paymentFormSubmit(params) {
        try {
            const token = await shift4.createToken(components)
            await handleTokenCreated(token, {
                ...params,
                card: token.id
            })
        } catch (error) {
            errorCallback(error)
        }
    }

    /**
     * tokenCreatedCallback edition for checkout block
     * 
     * @param request request params for threeDS
     * 
     * @example
     * const request = {
     *   amount: 900,
     *   currency: 'HKD',
     *   card: token.id,
     * }
     */
    async function handleTokenCreated(token, request) {
        if (['strict', 'frictionless'].includes(window.shift4Config.threeDS)) {
            try {
                const result = await shift4.verifyThreeDSecure(request)
                threeDSecureCompletedCallback(result)
            } catch (error) {
                errorCallback(error);
            }
        } else {
            setTokenAndContinue(token)
        }
    }

    window.shift4PaymentFormSubmit = block_paymentFormSubmit

    async function payWithApplePay(amount) {
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
        }
        const shoppingCartDetails = {
            total: {
                label: window.shift4Config.blogName,
                amount
            },
        }

        const paymentRequest = shift4.createPaymentRequest([applePayMethodData], shoppingCartDetails)
        try {
            const result = await paymentRequest.show()
            const applePayToken = result.details.token.paymentData
            return applePayToken
        } catch (error) {
            errorCallback(error)
        }
    }

    window.shift4PayWithApplePay = payWithApplePay
}
const event = new Event("shift4JsLoaded");
document.dispatchEvent(event);
window.shift4JsLoaded = true;
