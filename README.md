# Worldline Online Payments

**Payment methods**

Our plugin supports from the most global to very local payment methods to support business all over the world:

- **Credit and debit cards:** Visa, Mastercard, American Express, Carte Bancaire, Diners, JCB, Maestro, Union Pay International
- **Mobile payment methods:** Alipay+, Apple Pay, Google Pay, WeChatPay
- **Buy Now Pay Later:** Klarna, Oney 3x-4x, Oney Financement Long,
- **Alternative payment methods:** iDEAL, Bancontact, PayPal, Bizum, Cpay, Multibanco, Przelewy24, Twint, EPS, Bank Transfer by Worldline
- **Giftcards:** Intersolve gift cards, OneyBrandedGiftCard, Illicado, Giftcard Limonetik

**Process high-conversion payments**

Our Magento plugin makes use of our latest set of API, built specifically to ensure a high conversion rate for anyone using them:

- **Lightning mode:** Simply configure your credentials, and you’re ready to go!
- **Remember me:** use the native vault functionality from Magento to save your customers payment details and facilitate the
  journey of returning customers
- **Auto-format card number:** our payment page ensures that the card number is easily readable by any consumer using it
- **Automatic brand detection:** automatically detect the brand of the card used to avoid any error during the payment
  process.
- **Adaptative keyboard:** display the right keyboard on the right field on the payment page
- **28 supported languages** for the payment page
- **Compatible with advanced fraud solutions:** our plugin is fully compatible with the Worldline fraud solution. Build up your
  custom rules to ensure any legitimate transaction can go through, and block any fraudulent transaction.
- **Fully customizable payment pages:** the payment page is fully customizable through a powerful template builder allowing
  you to have access to both HTML and CSS of the payment page to fully blend in your own website style.
- **Authorize now, capture later:** we support separate authorization and capture, allowing you to manage the payment after
  the shipping while making sure all the necessary checks are performed on the card. We even support pre-authorization for
  sectors that need it!
- **Mobile first experience:** our solution is built to be fully responsive and will adapt to any device the customers may use.

**Provide the best checkout experience**

We made sure your customers remains as long as possible on your website and can retry seamlessly in case of a failed attempt, and
that at any time your Magento is fully up to date with the latest payment information

- **Basket is kept when clicking on “back”:** what is more frustrating that losing your basket when you click on “back” in your
  browser? Our plugin offers a native solution for this problematic!
- **Dynamic feedback:** our plugin will listen to feedback from Worldline to update the transaction status, while
  actively questioning the payment system to make sure no discrepancy occurs on your platform.
- **Submit basket details to the payment page:** on top of providing a best-in-class customer experience, this will also enable
  some specific payment methods like Klarna on your checkout page. The order data from the cart will be displayed on the
  Worldline Payment Page for enhanced buyer experience.
- **Integrate directly with our PWA extension:** we have also made available a Magento PWA extension that will help you build your storefront as easily as possible and introduce you to headless commerce.

**Follow up your transactions**

On top of allowing you to process successful transactions, our plugin will also provide you the tools needed to follow up failed
transactions and take actions accordingly

- **Customizable statuses:** even though we implemented the out-of-the-box Magento native flows, our plugin allow further
  customization to ensure the payments follow your flows and are adapted to any kind of business.
- **Manage maintenance operations:** our plugin allows you to easily maintain your transactions directly from the Magento
  Admin panel, making sure you can process multiple and partial captures and refunds directly from the native Magento
  interface.
- **Advanced logging system:** we have developed an advanced logging system that will allow you to easily retrieve all logs
  related to a specific transaction whenever you need it. This will allow you to further analyze your conversion numbers, and
  take enlightened business decisions accordingly.

## Main Extension for Adobe Commerce with all worldline solutions 

[![M2 Coding Standard](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/coding-standard.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/coding-standard.yml)
[![M2 Mess Detector](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/mess-detector.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/mess-detector.yml)

This is a main module that are used as a container to get all Worldline payment solutions:
- [credit card](https://github.com/wl-online-payments-direct/plugin-magento-creditcard)
- [hosted checkout](https://github.com/wl-online-payments-direct/plugin-magento-hostedcheckout)
- [redirect payments (single payment buttons)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments)

To install these solutions, you may use
[adobe commerce marketplace](https://marketplace.magento.com/worldline-module-magento-payment.html)
or install them from the GitHub.

Suggested packages:
- [recurring payments](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments)

### Change log:

#### 2.36.0
- Fix: Do not allow usage of decimals in the object cardPaymentMethodSpecificInput.paymentProduct130SpecificInput.threeDSecure.numberOfItems

#### 2.35.0
- Added: Possibility to auto-include primary webhooks URL in the payload of payment request, and to configure up to 4 additional endpoints.
- Fix Worldline Block/Info.php not compatible with Magento core Payment/Block/Info.php.

#### 2.34.0
- Improved: Data mapping to flag correctly exemptions requests to 3-D Secure.

#### 2.33.0
- Add new payment method: Pledg

#### 2.32.0
- Remove MealVouchers configuration from hosted checkout
- Fix mobile payment method information not being shown in order details

#### 2.31.0
- Update payment brand logos

#### 2.30.0
- Add quote ID to request payload
- Fix wrong IP address being sent on checkout
- Decrease maximum payment method logos
- Add compatibility with 2.4.8-p2

#### 2.29.0
- Fix issue with sending email

#### 2.28.0
- Fix wrong handling of payment specific information on order page

#### 2.27.0
- Fix comma separated email validation in notification settings

#### 2.26.0
- Fix issue with showing split payment amounts on order details page for Mealvoucher transactions
- Fix issue with showing Mealvoucher in full redirect

#### 2.25.0
- Fix logo issue for CB on checkout page
- Fix PHP >= 8.2 issue with not sending parameter by reference

#### 2.24.0
- Add Mealvoucher payment product
- Add CVCO (Cheque Vacances Connect Online) payment product

#### 2.23.0
- Add compatibility with PHP 8.4
- Update SDK version

#### 2.22.0
- Fixed order creation using Google Pay & Apple Pay

#### 2.21.0
- Update plugin translations

#### 2.20.0
- Added 3DS exemption types to the plugin

#### 2.19.0
- Fixed validation for HTML template ID configuration. It is no longer required to have extension on HTML templates.
- Fixed issue where items quantities in decimals were not taken into account.
- Improved handling of orders where the total amount does not match the sum of line items amount due to the rounding.

#### 2.18.0
- Fixed issue where FPT (Fixed Product Tax) rates were not taken into account.
- Update "wl-online-payments-direct/sdk-php" library to 5.16.1

#### 2.17.0
- Improved display of shipping costs on the payment page for Hosted Checkout and Redirect Payment.

#### 2.16.0
- Added trusted URLs to the CSP whitelist.
- Improved reliability of fallback cron job.
- Fixed credentials caching issue when simultaneously processing refunds for multiple merchant IDs.

#### 2.15.0
- Improved the order creation process by tracking multiple paymentIDs.
- Improved logging and exception handling when multiple payments are done for a single order.

#### 2.14.0
- Added new payment method "Bank Transfer by Worldline".
- Added the "Contact email" field to the feature suggestion form.
- Added compatibility with Php Sdk 5.10.0.
- Replaced legacy Alipay payment method with the new Alipay+.
- Replaced legacy WeChat Pay payment method with the new version.
- Fixed validation error when placing orders with Virtual/downloadable products.
- Fixed error when adding new shipping address on checkout.

#### 2.13.0
- Added email to customer and “Copy To” for "Auto Refund For Out Of Stock Orders" notifications.
- Added translations for French (Belgium), French (Switzerland) and Dutch (Belgium).
- Improved notifications so they are only sent once per event.
- Improved "Failed Orders Notifications" to avoid triggering on transaction status 46.
- Fixed "Redirect Payments" display issue after customer modifies shipping options.
- Fixed server error on checkout page when "Specific Currencies" are not aligned with Magento’s non-default currencies.

#### 2.12.0
- Added "Session Timeout" configuration for the hosted checkout page.
- Added "Allowed Number Of Payment Attempts" configuration for the hosted checkout page.
- Added compatibility with Php Sdk 5.8.2.
- Added refund refused notifications functionality.
- Fixed update of the credit memo status when the refund request was refused by acquirer.

#### 2.11.1
- Fixed issue with partial invoices and partial credit memos.
- Fixed transaction ID value for request to check if payment can be cancelled.

#### 2.11.0
- Added own branded gift card compatibility for Intersolve payment method.
- Added compatibility with Php Sdk 5.7.0.
- Modified plugin tab "dynamic order status synchronization" to “Settings & Notifications”.
- Fixed value determination process for "AddressIndicator" parameter.
- Fixed issues with creating orders by cron.
- Fixed issue with Magento confirmation page when using PayPal payment method.
- Fixed issue with auto refund for out-of-stock feature.
- Fixed issue when using a database prefix.

#### 2.10.0
- Added new payment method “Union Pay International".
- Added new payment method “Przelewy24".
- Added new payment method “EPS".
- Added new payment method “Twint".
- Added compatibility with Php Sdk 5.6.0.
- Added compatibility with Amasty Subscriptions & Recurring Payments extension 1.6.15.
- Improved plugin landing page "About Worldline".
- Improved Hosted Tokenization error message when transaction is declined.
- Improved concatenation of streetline1 and streetline2 for billing & shipping address.

#### 2.9.0
- Added new payment method “Giftcard Limonetik".
- Added new setting "Enable Sending Payment Refused Emails".
- Improved handling of Magento 2 display errors.
- Fixed hosted tokenization js link for production transactions.
- Fixed order creation issue on successful transactions.
- Fixed webhooks issue for rejected transactions with empty refund object.
- General code improvements.

#### 2.8.3
- Fixed issue of products with special pricing not displaying the original price in order view.
- Fixed issue with configurable product on cart restoration when user clicks the browser back button.
- Fixed issue with last payment id not fetched properly.
- Fixed issue where carts are restored incompletely.
- Fixed issue when customer attribute doesn't display in order after paying.
- Added customer address attributes validation before placing order.
- Added a setting to stop sending refusal emails.
- Added compatibility with Php Sdk 5.4.0.

#### 2.8.2
- Add support for the 5.3.0 version of PHP SDK.
- Fix connection credential caching.

#### 2.8.1
- Add support for the 5.1.0 version of PHP SDK.
- Add integration tests.
- General code improvements.

#### 2.8.0
- Add support for Magento 2.4.6.
- Add support for the 5.0.0 version of PHP SDK.
- Unhide API keys for the connection and webhooks.
- Unhide first 5 characters of the API secret keys for the connection and webhooks.
- Add a setting for Oney3x4x to manage the “Oney3x4x payment option” parameter.
- Hide Apple Pay if the customer cannot pay with it.
- Add support for the 13.0.0 PWA version and the surcharging functionality.
- Add integration tests.
- Add Amasty one-step checkout and surcharging functionality comparability.
- General code improvements.

#### 2.7.2
- Add fix for Adobe Commerce cloud instances.

#### 2.7.1
- Add auto refund functionality when an item has fallen out of stock when order is completed, additional admin notification will be sent when an auto refund attempt is made.
- Improve performance on the checkout cart page.
- Add backend address validation before payments.
- Add admin notifications in case an order creation fails.
- Add button called “Update data from Worldline” to update Worldline payment information in case it should be incomplete or missing.
- General code improvements and bug fixes.

#### 2.7.0
- Add surcharge functionality (for the Australian market).
- Add Sepa Direct Debit payment method.
- Add Sepa Direct Debit payment method for recurring payments.
- Add the ability to save the Sepa Direct Debit mandate and use it through the Magento vault.
- Improvements of the Oney3x4x payment method.
- Extract GraphQl into a dedicated extension.
- Add Integration tests for the credit card payment method.
- General code improvements and bug fixes.

#### 2.6.1
- Support the 13.0.0 version of PWA.

#### 2.6.0
- Add price restrictions for currencies having specific decimals rules (like JPY).
- Add Multibanco payment method.
- Add a functionality to limit the amounts purchased for the Subscriptions & Recurring payments.
- Add a link in the subscription emails to renew the token when it is expired or payment failed.
- Add marketing content to the readme file.
- Move 3-D Secure settings to the general tab.
- Change names and tooltips of the 3-D Secure settings.
- Add integration tests.
- Add unit tests.
- Add infrastructure for integration tests.
- General code improvements and bug fixes.

#### 2.5.1
- Add notification to merchants if order creation is failed.

#### 2.5.0
- Add the "Mealvouchers" payment method.
- Add the “Update Status” button for “View Memo”. This allows you to refresh in real time the status of your credit memos.
- Render webhooks updates in the order details.
- Add grid with Webhooks for debug purposes.
- Improve cancel and void actions logic.
- Add uninstall script.
- Update release notes.
- General code improvements and bug fixes.

#### 2.4.0
- Add "groupCards" functionality (for hosted checkout): group all card under one single payment button.
- Add payment method Intersolve and process the split payment.
- Improve Worldline payment box design: split in payment and fraud results.
- Add a feature to request 3DS exemption for transactions below 30 EUR.
- Add translations.
- Add integration tests (for credit card).
- General code improvements and bug fixes.

#### 2.3.1
- Bug fixes for Redirect Payments (single payment button).
- GraphQl improvements and support for Redirect Payments (single payment button).

#### 2.3.0
- Improve configuration settings for Redirect Payments (single payment button).
- Add new payment product Oney for Redirect Payments (single payment button).
- Option added to enforce Strong Customer Authentication for every 3DS request.
- Improved design of general settings page.
- General code improvements and bug fixes.
- Improvements and support for 2.3.x Magento versions.
- Support the 4.5.0 version of the Worldline SDK.

#### 2.2.3
- Improve work for multi website instances.

#### 2.2.2
- Improve the "waiting" page.
- Add the "pending" page.

#### 2.2.1
- Fix cron run time to prevent order duplication.

#### 2.2.0
- Support recurring payments based on Amasty recurring payment extension.
- Improve waiting page by adding an order summary block so that customers will always see what they have bought.
- Improve payment info block within Magento backend. Merchants can now manually refresh the info available to be sure it is always up to date.
- General improvements and bug fixes.

#### 2.1.1
- Hide the checkbox "save card" for iFrame checkout (Credit Card payment method) for guests and when the vault is disabled.
- Support version 4.3.3 of Worldline SDK.
- PWA improvements and support.
- Bug fixes and general code improvements.

#### 2.1.0
- New Redirect payments: integrate single payment buttons directly on Magento checkout.
- Waiting page has been added after payment is done to correctly process webhooks and create the order.
- Asyncronic order creation through get calls when webhooks suffer delay.
- Refund flow is improved for multi-website instances.
- Bancontact payment method implementation has been improved.
- General improvements and bug fixes.

#### 2.0.0
- Module segregation.
- Bug fix and general improvements.

#### 1.3.1
- GraphQL bug fix.

#### 1.3.0
- Credit card payment method improvements.
- New flow for the refunds: pending, refunded statuses for the refund items.
- Support Sepa methods for the hosted checkout payment method.
- Bug fix and general improvements.

#### 1.2.0
- Hosted checkout payment method improvements.
- Show Worldline information in order overview pages: frontend and backend.
- Add the Worldline request logs grid.
- Compatibility with Magento 2.3.7.
- Bug fix and general improvements.

#### 1.1.0
- PWA support.
- Log Worldline requests feature.
- Support of new version of the SDK.
- Compatibility with Magento 2.4.4.
- Bug fix.

#### 1.0.0
- Initial MVP version.
