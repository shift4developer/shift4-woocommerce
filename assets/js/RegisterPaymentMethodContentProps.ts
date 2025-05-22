import React from "react";

class NoticeContexts {
    static BILLING_ADDRESS = "wc/checkout/billing-address";
    static CART = "wc/cart";
    static CHECKOUT = "wc/checkout";
    static CHECKOUT_ACTIONS = "wc/checkout/checkout-actions";
    static CONTACT_INFORMATION = "wc/checkout/contact-information";
    static EXPRESS_PAYMENTS = "wc/checkout/express-payments";
    static ORDER_INFORMATION = "wc/checkout/order-information";
    static PAYMENTS = "wc/checkout/payments";
    static SHIPPING_ADDRESS = "wc/checkout/shipping-address";
    static SHIPPING_METHODS = "wc/checkout/shipping-methods";
}

enum ResponseTypes {
    ERROR = "error",
    FAIL =  "failure",
    SUCCESS = "success"
}

export interface RegisterPaymentMethodContentProps {
    activePaymentMethod: string;
    billing: {
        cartTotal: {
            label: string;
            value: number;
        };
        currency: {
            code: string;
            symbol: string;
            thousandSeparator: string;
            decimalSeparator: string;
            minorUnit: number;
            prefix: string;
            suffix: string;
        }
    };
    components: {
    LoadingMask: React.FunctionComponent;
    PaymentMethodIcons: React.FunctionComponent;
    PaymentMethodLabel: React.FunctionComponent;
    ValidationInputError: React.FunctionComponent;
    };
    emitResponse: {
        noticeContexts: typeof NoticeContexts;
        responseTypes: typeof ResponseTypes;
    };
    eventRegistration: {
        onCheckoutAfterProcessingWithError: () => () => void;
        onCheckoutAfterProcessingWithSuccess: () => () => void;
        onCheckoutBeforeProcessing: () => () => void;
        onCheckoutFail: (onFail: () => void) => () => void;
        onCheckoutSuccess: (onSuccess: () => void) => () => void;
        onCheckoutValidation: (onValidation: () => void) => () => void;
        onCheckoutValidationBeforeProcessing: () => () => void;
        onPaymentProcessing: () => () => void;
        onPaymentSetup: (
            setup: () => Promise<{ type: ResponseTypes; meta?: { paymentMethodData: any; }; message?: string; } | void>
        ) => () => void;
        onShippingRateFail: () => () => void;
        onShippingRateSelectFail: () => () => void;
        onShippingRateSelectSuccess: () => () => void;
        onShippingRateSuccess: () => () => void;
    };
    onSubmit: () => void;
    onClose: () => void;
    setExpressPaymentError: (error: any) => void;
}