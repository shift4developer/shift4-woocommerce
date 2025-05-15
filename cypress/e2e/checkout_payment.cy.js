describe('WooCommerce Checkout Flow with Shift4', () => {
    it('completes checkout from shop to order confirmation', () => {
        cy.visit('/')

        // click Shop nav item in homepage
        cy.contains('Shop').click()

        // add to cart
        cy.intercept('GET', '**/wc/store/v1/cart').as('getCart')
        cy.contains('Add to cart').click()
        cy.wait('@getCart')

        // wait a second for the update and then go to the checkout page
        cy.wait(1000)
        cy.contains('Cart').click()
        cy.contains('Proceed to Checkout').click()

        // input fields
        cy.get('#email').type('test@shift4.com')
        cy.get('#shipping-first_name').type('F')
        cy.get('#shipping-last_name').type('L')
        cy.get('#shipping-address_1').type('A')
        cy.get('#shipping-city').type('TD', { force: true })
        cy.get('#shipping-postcode').type('PC', { force: true })
        cy.get('#shipping-phone').type('P', { force: true })

        // input card fields in the iframes
        cy.frameLoaded('iframe[name="__privateShift4Frame0"]')
        cy.iframe('iframe[name="__privateShift4Frame0"]').find('input[id="card-number-input-unknown"]').type('4242000000000083')

        cy.frameLoaded('iframe[name="__privateShift4Frame1"]')
        cy.iframe('iframe[name="__privateShift4Frame1"]').find('input[id="exp-date-input"]').type('1229')

        cy.frameLoaded('iframe[name="__privateShift4Frame2"]')
        cy.iframe('iframe[name="__privateShift4Frame2"]').find('input[id="cvc-input"]').type('123')

        // place order
        cy.intercept('POST', '**/wc/store/v1/checkout**').as('checkout')
        cy.contains('Place Order').click()
        cy.wait('@checkout')

        // check the result
        cy.contains('h1', 'Order received').should('be.visible')
    })
})