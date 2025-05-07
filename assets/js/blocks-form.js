import React from 'react'
import { useEffect, useRef } from '@wordpress/element'

const Shift4PaymentForm = ({ eventRegistration, emitResponse, billing }) => {
    const { cartTotal, currency } = billing
    const formRef = useRef(null)
    const paymentMethodDataRef = useRef(null)
    const { onPaymentSetup } = eventRegistration

    useEffect(() => {
        if (!window.shift4Initialised) {

            const initialize = () => {
                if (typeof initShift4 === 'function') {
                    const blockOptions = {
                        paymentMethodDataRef
                    }
                    initShift4(blockOptions)
                    window.shift4Initialised = true
                    window.shift4UpdatedCheckout()
                }
            }

            if (window.shift4JsLoaded) {
                initialize()
            } else {
                document.addEventListener('shift4JsLoaded', initialize)
            }
        }
        window.shift4UpdatedCheckout()
        const unsubscribe = onPaymentSetup(async () => {
            window.clearError()
            await window.shift4PaymentFormSubmit({
                amount: cartTotal.value,
                currency: currency.code
            })
            if (paymentMethodDataRef.current) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            ...paymentMethodDataRef.current
                        }
                    },
                }
            }
            return {
                type: emitResponse.responseTypes.ERROR
            }
        })
        return () => {
            unsubscribe()
        }
    }, [
        emitResponse.responseTypes.ERROR,
        emitResponse.responseTypes.SUCCESS,
        onPaymentSetup,
    ])

    return (
        <div id="shift4-payment-form" ref={formRef}>
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
        </div>
    )
}

export default Shift4PaymentForm
