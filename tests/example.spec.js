// @ts-check
import { test, expect } from '@playwright/test';

test('shop', async ({ page }) => {
    await page.goto('/')
    await page.click('text="Shop"')

    await Promise.all([
        page.waitForResponse(response =>
            response.url().includes('?rest_route=%2Fwc%2Fstore%2Fv1%2Fcart') &&
            response.request().method() === 'GET' &&
            response.status() === 200
        ),
        page.click('text="Add to cart"'),
    ])

    await page.waitForTimeout(1000);
    await page.click('text="Cart"')
    await page.click('text="Proceed to Checkout"')

    await page.fill('#email', 'test@shift4.com')
    await page.fill('#shipping-first_name', 'F')
    await page.fill('#shipping-last_name', 'L')
    await page.fill('#shipping-address_1', 'A')
    await page.fill('#shipping-city', 'TD')
    await page.fill('#shipping-postcode', 'PC')
    await page.fill('#shipping-phone', 'P')

    const cardFrame = page.frameLocator('iframe[name="__privateShift4Frame0"]')
    await cardFrame.locator('input[id="card-number-input-unknown"]').fill('4242000000000083');
    const expiryFrame = page.frameLocator('iframe[name="__privateShift4Frame1"]')
    await expiryFrame.locator('input[id="exp-date-input"]').fill('1229');
    const cvcFrame = page.frameLocator('iframe[name="__privateShift4Frame2"]')
    await cvcFrame.locator('input[id="cvc-input"]').fill('123');

    await Promise.all([
        page.waitForResponse(resp =>
            resp.url().includes('rest_route=/wc/store/v1/checkout&_locale=site') &&
            resp.status() === 200),
        page.waitForNavigation(), // Waits for the next navigation.
        page.click('text="Place Order"'), // Triggers a navigation after a timeout.
    ]);

    await expect(page.getByRole('heading', { name: 'Order received' })).toBeVisible();
});