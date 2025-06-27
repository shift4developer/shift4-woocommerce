import React from 'react'
import { useEffect, useRef } from '@wordpress/element'
import { RegisterPaymentMethodContentProps } from './RegisterPaymentMethodContentProps';
const { useSelect } = window.wp.data;
const { validationStore } = window.wc.wcBlocksData;

export const Shift4ApplePay = (props: RegisterPaymentMethodContentProps) => {
    if (!props) return null;
    const { activePaymentMethod, billing, emitResponse, eventRegistration, onSubmit, onClose } = props
    const { onPaymentSetup, onCheckoutFail, onCheckoutSuccess, onCheckoutValidation } = eventRegistration
    if (!billing) return null;
    const tokenInputRef = useRef<HTMLInputElement | null>(null)
    const buttonRef = useRef<HTMLButtonElement | null>(null)
    const { cartTotal, currency } = billing
    const paymentResponseRef = useRef<PaymentResponse | null>(null)

    const hasValidationErrors = useSelect((select) => select(validationStore).hasValidationErrors(), [validationStore]);

    useEffect(() => {
        if (!window.shift4Initialised) {
            const initialize = () => {
                if (typeof window.initShift4 === 'function') {
                    const blockOptions = {};
                    window.initShift4(blockOptions)
                    window.shift4Initialised = true
                }
            }

            if (window.shift4JsLoaded) {
                initialize()
            } else {
                document.addEventListener('shift4JsLoaded', initialize)
            }
        }

        const button = buttonRef.current
        if (button) {
            button.addEventListener('click', handleClick)
        }

        return () => {
            if (button) {
                button.removeEventListener('click', handleClick)
            }
        }
    }, [])

    const handlePayWithApplePay = async () => {
        const paymentResponse = await window.shift4PayWithApplePay({
            value: (cartTotal.value / Math.pow(10, currency.minorUnit)).toFixed(currency.minorUnit),
            currency: currency.code
        })

        if (tokenInputRef.current) {
            tokenInputRef.current.value = JSON.stringify(paymentResponse.details.token.paymentData)
        }

        return paymentResponse;
    }

    const handleClick = async () => {
        onSubmit();
    }

    useEffect(() => {
        const unsubscribePaymentSetup = onPaymentSetup(async () => {
            paymentResponseRef.current = await handlePayWithApplePay();
            try {
                if (tokenInputRef.current?.value) {
                    return {
                        type: emitResponse.responseTypes.SUCCESS,
                        meta: {
                            paymentMethodData: {
                                shift4_applepay_token: tokenInputRef.current.value
                            }
                        },
                    }
                }
            } catch (ex) {
                const error = ex instanceof Error ? ex : new Error('An unknown error occurred while processing the payment.');
                onClose()
                paymentResponseRef.current.complete('fail');
                return {
                    type: emitResponse.responseTypes.ERROR,
                    message: error.message || 'An error occurred while processing the payment.'
                }
            }
        });

        const unsubscribeCheckoutSuccess = onCheckoutSuccess(async () => {
            if (paymentResponseRef.current) {
                await paymentResponseRef.current.complete('success');
            }
        });

        const unsubscribeCheckoutFail = onCheckoutFail(async () => {
            if (paymentResponseRef.current) {
                await paymentResponseRef.current.complete('fail');
            }
        });

        return () => {
            unsubscribeCheckoutSuccess();
            unsubscribeCheckoutFail();
            unsubscribePaymentSetup();
        }
    }, [onCheckoutFail, onCheckoutSuccess, onCheckoutValidation, onPaymentSetup, emitResponse.responseTypes.ERROR, emitResponse.responseTypes.SUCCESS, onClose])

    useEffect(() => {
        const subtmitButton = document.querySelector<HTMLButtonElement>('button.wc-block-components-checkout-place-order-button');
        if (activePaymentMethod === 'shift4_applepay') {
            subtmitButton?.style.setProperty('display', 'none', 'important');
        } else {
            subtmitButton?.style.removeProperty('display');
        }

        return () => {
            if (subtmitButton) {
                subtmitButton.style.removeProperty('display');
            }
        }
    }, [activePaymentMethod])



    return (
        <div style={hasValidationErrors ? {
            opacity: 0.2,
            pointerEvents: 'none',
        } : undefined}>
            {/* @ts-ignore */}
            <apple-pay-button
                id="apple-pay-submit-button"
                buttonstyle="black"
                type="buy"
                locale="en-US"
                class="w-full"
                lang="en-US"
                ref={buttonRef}
            />
            <input
                type="hidden"
                name="shift4_applepay_token"
                ref={tokenInputRef}
            />
        </div>
    )
}
