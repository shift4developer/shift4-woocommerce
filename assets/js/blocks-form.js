import React from 'react';
import { useEffect, useRef } from '@wordpress/element';

const Shift4PaymentForm = ( { paymentStatus } ) => {
    const formRef = useRef(null);
    const shift4FormSelector = '#shift4-payment-form';
    let components;

    useEffect(() => {
        console.log('useEffect')
        if (!window.shift4Initialised) {
            console.log('window.shift4Config: ' + window.shift4Config)
            
            shift4 = window.Shift4(window.shift4Config.publicKey);

            try {
                components = shift4.createComponentGroup().automount(shift4FormSelector);
            } catch (error) {
                console.error('Error mounting Shift4 components:', error);
            }

            const initialize = () => {
                if (typeof initShift4 === 'function') {
                    initShift4();
                    window.shift4Initialised = true;
                }
            };

            if (window.shift4JsLoaded) {
                initialize();
            } else {
                document.addEventListener('shift4JsLoaded', initialize);
            }
        } 
        if (isProcessing) {
            console.log(shift4)
            shift4.createToken(components)
                .then(tokenCreatedCallback)
                .catch(errorCallback);
        }
    }, []);

    return (
        <div id="shift4-payment-form" ref={formRef}
            data-amount={window.shift4Amount}
            data-currency={window.shift4Currency}
        >
            <div className="shift4-payment-field">
                <label className="shift4-payment-number-label">
                    Card number
                </label>
                <div className="shift4-payment-input shift4-payment-number-input" data-shift4="number"></div>
            </div>
            <div className="shift4-payment-fields">
                <div className="shift4-payment-field">
                    <label className="shift4-payment-expiry-label">
                        Expiration
                    </label>
                    <div className="shift4-payment-input shift4-payment-expiry-input" data-shift4="expiry"></div>
                </div>
                <div className="shift4-payment-field">
                    <label className="shift4-payment-cvv-label">
                        Cvv
                    </label>
                    <div className="shift4-payment-input shift4-payment-cvv-input" data-shift4="cvc"></div>
                </div>
            </div>
            <input type="hidden" name="shift4_card_token" id="shift4_card_token" />
        </div>
    );
};

export default Shift4PaymentForm;
