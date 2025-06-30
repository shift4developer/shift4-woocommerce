interface ApplePaySession {
  canMakePayments: () => boolean;
}

declare global {
  namespace JSX {
    interface IntrinsicElements {
      'apple-pay-button': React.DetailedHTMLProps<React.HTMLAttributes<HTMLElement>, HTMLElement>;
    }
  }
}

interface Window {
    ApplePaySession: ApplePaySession | undefined;
    clearError: () => void;
    initShift4: (options: {
        paymentMethodDataRef?: React.MutableRefObject<any>;
    }) => void;
    shift4Config: {
        blogName: string;
        threeDSValidationMessage: string;
        publicKey: string;
    };
    shift4CardSettings: {
        cardTitle: string;
        enabled: boolean;
        savedCardsEnabled: string;
        threeDS: string;
    };
    shift4ApplePaySettings: {
        applePayTitle: string;
        enabled: boolean;
    };
    shift4Initialised: boolean;
    shift4JsLoaded: boolean;
    shift4PaymentFormSubmit: (options: {
        amount: number;
        card?: string;
        currency: string;
        merchantAccountId?: string;
    }) => Promise<void>;
    shift4PayWithApplePay: (options: {
        value: string;
        currency: string;
    }) => Promise<PaymentResponse>;
    shift4UpdatedCheckout: () => void;
    wc: {
        wcBlocksRegistry: {
            registerPaymentMethod: (method: any) => void;
            registerExpressPaymentMethod: (method: any) => void;
        };
        wcBlocksData: {
            validationStore: {
              hasValidationErrors: () => boolean;
            };
        };
    };
    wp: {
        data: {
            useSelect: <Return>(
              selector: (select: (<Store>(store: Store) => Store)) => Return,
              deps?: any[]
            ) => Return;
        }
    }
}
