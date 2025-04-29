const { registerPaymentMethod } = window.wc.wcBlocksRegistry;
const { registerExpressPaymentMethod } = window.wc.wcBlocksRegistry;
const { createElement } = window.wp.element;
import React from 'react';
import Shift4PaymentForm from './blocks-form';

// Register Shift4 Apple Pay.
registerExpressPaymentMethod({
    name: 'shift4_applepay',
    label: 'Apple Pay (Shift4)',
    content: createElement('p', null, 'Apple Pay (Shift4)'),
    edit: createElement('p', null, ''),
    canMakePayment: () => true
});

// Register Shift4 Credit Card.
registerPaymentMethod({
    name: 'shift4_card',
    label: 'Credit Card (Shift4)',
    content: <Shift4PaymentForm />,
    edit: createElement('p', null, ''),
    savedTokenComponent: createElement('p', null, 'simple savedTokenComponent'),
    canMakePayment: () => true,
    ariaLabel: 'Shift4 Credit Card payment method'
});
