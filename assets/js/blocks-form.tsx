import React from 'react'
import { useEffect } from '@wordpress/element'
import { RegisterPaymentMethodContentProps } from './RegisterPaymentMethodContentProps'

export const Shift4PaymentForm = ({ eventRegistration, emitResponse, billing }: RegisterPaymentMethodContentProps) => {
    const { cartTotal, currency } = billing
    const { onPaymentSetup } = eventRegistration

    useEffect(() => {
        if (!window.shift4Initialised) {
            const initialize = () => {
                if (typeof window.initShift4 === 'function') {
                    window.initShift4()
                    window.shift4Initialised = true
                    window.shift4UpdatedCheckout()
                }
            }

            if (window.shift4JsLoaded) {
                initialize()
            } else {
                document.addEventListener('shift4JsLoaded', initialize)
            }
        } else {
            window.shift4UpdatedCheckout()
        }
        const unsubscribe = onPaymentSetup(async () => {
            window.clearError()
            await window.shift4PaymentFormSubmit({
                amount: cartTotal.value,
                currency: currency.code
            })

            const data = window.paymentMethodDataRef.current;

            if (!!data && Object.keys(data).length > 0) {
                return {
                    type: emitResponse.responseTypes.SUCCESS,
                    meta: {
                        paymentMethodData: {
                            ...window.paymentMethodDataRef.current
                        }
                    },
                }
            }
            return {
                type: emitResponse.responseTypes.ERROR,
                message: 'There was an error'
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
        <div id="shift4-payment-form">
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
