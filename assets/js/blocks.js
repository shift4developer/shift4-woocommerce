const { registerPaymentMethod } = window.wc.wcBlocksRegistry
// const { registerExpressPaymentMethod } = window.wc.wcBlocksRegistry
const { createElement } = window.wp.element
const { cardTitle, savedCardsEnabled } = window.shift4Config

import React from 'react'
import Shift4PaymentForm from './blocks-form'
// import Shift4ApplePay from './blocks-apple-pay'

// Register Shift4 Apple Pay.
// registerExpressPaymentMethod({
//     name: 'shift4_applepay',
//     label: 'Apple Pay (Shift4)',
//     edit: <p>Apple Pay (Shift4)</p>,
//     content: <Shift4ApplePay />,
//     canMakePayment: () => {
//         return !!(window.ApplePaySession && window.ApplePaySession.canMakePayments())
//     }
// })

// Register Shift4 Credit Card.
registerPaymentMethod({
    name: 'shift4_card',
    label: cardTitle,
    content: <Shift4PaymentForm />,
    edit: <p>Credit Card (Shift4)'</p>,
    canMakePayment: () => true,
    ariaLabel: 'Shift4 Credit Card payment method',
    supports: {
        features: ['products'],
        showSavedCards: savedCardsEnabled === 'yes' ? true : false,
        showSaveOption: savedCardsEnabled === 'yes' ? true : false
    }
})
