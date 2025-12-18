=== Tranzak Payment Gateway ===

Contributors: tranzak, anseloh

Tags: payment, MTN momo, Orange money, Cameroon, WooCommerce, Payment, Gateway

Tested up to: 6.4.2

Requires at least: 5.7.9

Requires PHP:       7.3

Version:           1.0.1

License: GPLv2 or later

License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Tranzak Payment Gateway would help you receive payments to seamlessly and also integrates with woolCommerce.


### Supported payment methods include: 

- Mobile Wallet (MTN Mobile Money, Orange Money, and other Mobile Wallets across Africa)
- Bank Transfer
- Credit / Debit Cards (Visa & Master Card)

### Features

- Same page payment without redirection to Tranzak Payment Gateway (Available only to users who passed Tranzak KYC)
- Secure and robust payments gateway with support for webhooks and authentication for inter server communication.
- Seamless integration with WooCommerce.
- Request payment / donation with provision of short codes and progress tracking in case of fun raising.


== Installation ==
1. Download and install the plugin from the wordpress repository;
2. Activate the plugin through the `Plugins` menu in WordPress
3. Open settings from the `Tranzak` found on the left menu
4. Fill in the required information related to your tranzak account (`App ID`, `App key`, `Webhook`, `Auth key`, `Environment` and `Payment currency code`)
5. For wooCommerce, go to the WooCommerce menu > Settings > Payment and enable your preferred payment method. 

== Domains and uses ==
- [pay.tranzak.net (production)](pay.tranzak.net) / [sandbox.pay.tranzak.net (sandbox)](sandbox.pay.tranzak.net) would redirect user to the Tranzak payment authorization gateway to complete payment and would redirect to a dedicated page after payment was successful or failed
- [dsapi.tranzak.me (production)](dsapi.tranzak.me) / [sandbox.dsapi.tranzak.me (sandbox)](sandbox.dsapi.tranzak.me) is used to authenticate the credentials entered in the plugin settings page and also to create/update/view payment requests or transaction
- [api.tranzak.me](api.tranzak.me) is used to updated user's account when the plugin is activated
- [community.tranzak.net](community.tranzak.net) is a link to our community / forum where users can ask or find answers to any issue they are facing
- [biz.tranzak.me](Merchant portal) is a portal for creating merchants accounts and changing merchant configurations
- [https://wa.me/237674460261](Link to our customer support WhatApp) help users reach us through WhatsApp if they run into issues with the plugin
- [https://www.youtube.com/@tranzak790](Our youtube channel) this link would redirect you to our YouTube channel where you can find tutorials and tips for using our services


== Changelog ==

= v1.0.1 =

* Security patches 