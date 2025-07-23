interface ApplePaySession {
  canMakePayments: () => boolean;
}

interface Shift4 {
  createComponent: CreateComponent;
  createComponentGroup: (options = {}) => Shift4ComponentGroup;
  createToken: (componentOrToken: Shift4ComponentGroup | Shift4Component | PaymentResponse, tokenData: TokenRequest = {}) => Promise<Token>;
  verifyThreeDSecure: (request: any) => Promise<Token>;
  createPaymentRequest: (methodData: PaymentMethodData[], details: PaymentDetailsInit) => PaymentRequest;
}

type CreateComponent = (type: string, options = Record<string, any>) => Shift4Component;

interface Shift4Component {
  focus: () => void;
  mount: (selector: string | HTMLElement) => void;
  updateOptions: (options: Record<string, any>) => void;
  clear: () => void;
}

interface Shift4ComponentGroup {
  createComponent: CreateComponent;
  automount: (selector: string | HTMLElement) => Shift4ComponentGroup;
  updateComponentOptions: (type: string, options = Record<string, any>) =>  void;
}

interface Token {
  tokenId: string;
}

interface TokenRequest {
    googlePay?: {
        token: string;
    }
    applePay?: {
        token: object | string;
    }
    fraudCheckData?: {
        phone?: string;
        email?: string;
        sessionId?: string;
    }
}

declare global {
  namespace JSX {
    interface IntrinsicElements {
      'apple-pay-button': React.DetailedHTMLProps<React.HTMLAttributes<HTMLElement>, HTMLElement>;
    }
  }
  namespace Shift4 {
    interface Shift4 {
      createComponent: CreateComponent;
      createComponentGroup: (options?: Record<string, any>) => Shift4ComponentGroup;
      createToken: (componentOrToken: Shift4ComponentGroup | Shift4Component | PaymentResponse, tokenData?: TokenRequest) => Promise<Token>;
      verifyThreeDSecure: (request: any) => Promise<Token>;
      createPaymentRequest: (methodData: PaymentMethodData[], details: PaymentDetailsInit) => PaymentRequest;
    }
  }
}

interface Window {
    ApplePaySession: ApplePaySession | undefined;
    clearError: () => void;
    initShift4: () => void;
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
    },
    paymentMethodDataRef: {
      current: Record<string, any>;
    }
}
