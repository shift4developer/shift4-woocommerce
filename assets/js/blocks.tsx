import React from 'react'
const { registerPaymentMethod } = window.wc.wcBlocksRegistry
const { savedCardsEnabled } = window.shift4CardSettings

import { Shift4PaymentForm } from './blocks-form'
import { Shift4ApplePay } from './blocks-apple-pay'

registerPaymentMethod({
    name: 'shift4_applepay',
    label: window.shift4ApplePaySettings.applePayTitle,
    edit: <p>Apple Pay (Shift4)</p>,
    // @ts-ignore
    content: <Shift4ApplePay />,
    ariaLabel: 'Apple Pay payment method',
    canMakePayment: () => {
        const isEnabled = window.ApplePaySession && window.ApplePaySession.canMakePayments() && window.shift4ApplePaySettings.enabled;

        return !!isEnabled;
    }
})

registerPaymentMethod({
    name: 'shift4_card',
    label: window.shift4CardSettings.cardTitle,
    // @ts-ignore
    content: <Shift4PaymentForm />,
    edit: <p>Credit Card (Shift4)'</p>,
    canMakePayment: () => window.shift4CardSettings.enabled,
    ariaLabel: 'Shift4 Credit Card payment method',
    supports: {
        features: ['products'],
        showSavedCards: savedCardsEnabled === 'yes' ? true : false,
        showSaveOption: savedCardsEnabled === 'yes' ? true : false
    }
})
