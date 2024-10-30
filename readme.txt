=== Maypay for WooCommerce ===
Contributors: maypayapp
Tags: woocommerce, payment method, Maypay
Requires at least: 5.2
Tested up to: 6.4.2
Version: 1.0.1
Stable tag: 1.0.1
Requires PHP: 7.2
License: GPL v2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Maypay integration for WooCommerce.

== Description ==

Maypay for WooCommerce is a plugin designed to seamlessly integrate Maypay as a payment service for WooCommerce. Maypay itself is a unique payment platform where users have the opportunity to potentially win what they're purchasing by participating in a contest. With this plugin, Maypay becomes an available payment method within WooCommerce, granting customers the option to select Maypay during the checkout process. During checkout, the plugin displays the Maypay payment button. When clicked, this button opens a payment frame and connects with Maypay's servers to handle the payment response securely.

To ensure proper configuration of the plugin, a business account on https://business.maypay.com is mandatory.

In order to facilitate the payment flow effectively, Maypay for WooCommerce relies on two external services:

- Cloud Function Server (https://europe-west1-maypay-app.cloudfunctions.net): Utilized by the plugin for creating new payment requests or processing refunds.
- Maypay Redirect Server (https://maypay-app.web.app): Employed to present the payment frame to the user.
- Additionally, the plugin exposes a protected webhook (https://yourdomain.com/?rest_route=/maypay/v1/hook) to manage asynchronous responses from the cloud functions server regarding payment and refund statuses, ensuring seamless updating of WooCommerce orders.

Prior to use, we recommend reviewing the service terms at https://www.maypay.com/termini-e-condizioni/ and our privacy policy at https://www.maypay.com/privacy-policy/ to understand how your data is handled.

== Features ==

- Seamless integration of Maypay payment services into WooCommerce.
- Allows customers to choose Maypay as their payment method.
- Provides a brief description of Maypay as a payment option.
- Supports refunds for Maypay payments.

== Installation ==

1. Upload the `maypay` folder to the `/wp-content/plugins/` directory.
2. Activate the "Maypay for WooCommerce" plugin through the 'Plugins' menu in WordPress.
3. Go to the WooCommerce settings page and navigate to the "Payments" tab.
4. Enable the "Maypay" payment method and configure the plugin settings.
5. Save the changes and start accepting payments through Maypay.

== Frequently Asked Questions ==

= What is Maypay? =

Maypay is a payment platform that provides users with the chance to win their purchase by participating in a contest.

= How can I enable Maypay as a payment option? =

To enable Maypay, you must first register a business account at https://business.maypay.com, then install and activate the "Maypay for WooCommerce" plugin. Finally, navigate to the WooCommerce settings page, access the "Payments" tab, and enable the "Maypay" payment method. Configure the plugin settings with your Maypay StoreID, Public, and Private keys.

= Can I refund Maypay payments? =

Yes, the plugin supports refunds for Maypay payments, which can be processed through the WooCommerce order management interface.

== Changelog ==

= 1.0.1 =
* Updated readme.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.1 =
Updated readme.

= 1.0.0 =
Initial release of the Maypay for WooCommerce plugin.