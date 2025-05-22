import { useEffect, useRef } from '@wordpress/element'

const Shift4ApplePay = ({ onClick, onClose, onSubmit, billing }) => {
    const tokenInputRef = useRef(null)
    const buttonRef = useRef(null)
    const { cartTotal, currency } = billing
    const paymentMethodDataRef = useRef(null)

    useEffect(() => {
        if (!window.shift4Initialised) {

            const initialize = () => {
                if (typeof initShift4 === 'function') {
                    const blockOptions = {
                        paymentMethodDataRef
                    }
                    initShift4(blockOptions)
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

    const handleClick = async () => {

        // taken over payment processing
        onClick()

        try {
            const applePayToken = await window.shift4PayWithApplePay({
                value: (cartTotal.value / Math.pow(10, currency.minorUnit)).toFixed(currency.minorUnit),
                currency: currency.code
            })

            // save result to the form
            if (tokenInputRef.current) {
                tokenInputRef.current.value = JSON.stringify(applePayToken)
            }

            // submits the checkout and begins processing
            onSubmit()
        } catch (error) {
            console.error('Apple Pay Failed', error)
            onClose()
        }
    }

    return (
        <div>
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

export default Shift4ApplePay