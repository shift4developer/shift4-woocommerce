=== Shift4 for WooCommerce ===
Contributors: shift4
Tags: online store, shop, sell online, shift4, woocommerce
Requires at least: 6.7
Tested up to: 6.8
Requires PHP: 8.0
Stable tag: 1.0.8
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Plugins enables payments in your WooCommerce store using two Shift4 methods.

== Description ==

The [Shift4 for WooCommerce plugin](https://dev.shift4.com/docs/plugins/woo-commerce/) enables payments in your WooCommerce store using two methods: Apple Pay and card payments processed by Shift4. By leveraging this plugin, you will be able to accept payments safely and securely.

Features:
* Credit Card Payments
* Saved Cards
* Apple Pay
* 3D Secure
* Full & Partial Refunds
* Automatic settlement or authorise only transactions
* WooCommerce Classic Checkout
* WooCommerce Block-based Checkout
* Order via admin

== Installation ==

1. Log in to your WordPress admin panel and go to Plugins -> Add New
2. Type **Shift4 for WooCommerce** in the search box and click on the search button.
3. Find Shift4 for WooCommerce plugin.
4. Then click on Install Now and then activate the plugin.

OR

1. Download and save the **Shift4 for WooCommerce** plugin to your hard disk.
2. Login to your WordPress and go to the Add Plugins page.
3. Click the Upload Plugin button to upload the zip.
4. Click Install Now to install and activate the plugin.

== Configuration ==

WooCommerce > Settings > Payments

This configuration is shared across both Card and Apple Pay sections, therefore it only needs to be updated and configured in one area and the settings will be shared. 
* Public Key - Your Shift4 account Public Key. This can be located in your Shift4 account under Account Settings > API Keys
* Secret Key - Your Shift4 account Secret Key. This can be located in your Shift4 account under Account Settings > API Keys
* Capture Strategy - To set the capture of the payment to be immediate or set it to authorise only to be captured manually later.  
* Debug Logging  - With debug logging enabled all data about charge requests will be recorded to Shift4_Payments so that unexpected behaviour can be identified. When debug is disabled decline responses will still be recorded.

Shift4 Card Payment
* Enable / Disable - To enable or disable card payments via Shift4.
* Title - Title of the payment method that will show in the checkout to the customer. 
    * 3DS Mode - To enable or disable 3DS2 verification on card payments. 
    * Disabled - 3DS is completely disabled
    * Frictionless - 3DS will only be used if card 
* Strict - Only cards that support 3DS will be permitted
* Card Vaulting - To enable the ability for logged in customers to save their card details to use on future transactions.

Shift4 Apple Pay
* Enable / Disable - To enable or disable Apple Pay payments via Shift4.
* Title - Title of the payment method that will show in the checkout to the customer.  


== Supported Payment Methods ==

**Card**
Need to ensure card types are enabled in your Shift4 Account. Card Types that Shift4 supports: 
* Visa
* Mastercard
* Discover
* American Express
* Diners Club
* JCB

**Saved Card**
Only customers with an account will be able to store their card for future use. 

**Apple Pay**
Need to enable Apple Pay in your Shift4 Account - do to this please email: devsupport@shift4.com Your certificate merchant certificate needs to be registered either through your Shift4 Dashboard or at Apple's webpage. Please follow our manual for detailed instructions.


== Testing ==

For testing your integration you first need a Shift4 development account which you can setup here: [https://dev.shift4.com/](https://dev.shift4.com/)
* In your test WooCommerce  store, install the Shift4 for WooCommerce plugin.
* Using your Shift4 Developer API keys , head to Settings and add in your Test API keys and enable the relevant payment methods
* You can now test Shift4 transaction in your WooCommerce test store 


== Changelog ==

2025-07-24 - version 1.0.8
* Extended settings to option with action to take when fraud detected

2025-07-21 - version 1.0.7
* Added support for WooCommerce v10.x.x

2025-07-01 - version 1.0.6
* Adjusted plugin to Wordpress Guidelines before subbmiting to Wordpress SVN repository

2025-04-25 - version 1.0.4
* Added: Support HPOS(High-Performance Order Storage)

2025-04-10 - version 1.0.3
* Fixed: Card input fields were missing in the Shift4 payment method after changing the product quantity on the WooCommerce checkout page.
* Fixed: Submit button remained in a loading state indefinitely when an error occurred during checkout.
* Fixed: Error message was not displayed if the user navigated between steps on the WooCommerce checkout page.

2025-04-04 - version 1.0.2
* Fixed an issue that duplicates card fields

2024-04-08 - version 1.0.1
* Fixed an issue that broke the payment form in scenarios where Shift4 was not available on the initial page load,
* Updated text domain to correctly match plugin file,
* Added "version", "Requires PHP" and "WC tested up to" plugin headers.

2024-02-29 - version 1.0.0
* Initial release.