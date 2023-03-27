# Worldline Online Payments

**Payment methods**

Our plugin supports from the most global to very local payment methods to support business all over the world:

- **Credit and debit cards:** Visa, Mastercard, American Express, Carte Bancaire, Diners, JCB, Maestro
- **Mobile payment methods:** Alipay, Apple Pay, Google Pay, WeChatPay
- **Buy Now Pay Later:** Klarna, Oney 3x-4x, Oney Financement Long,
- **Alternative payment methods:** iDEAL, Bancontact, Paypal, Bizum, Cpay, Multibanco
- **Giftcards:** Intersolve gift cards, OneyBrandedGiftCard, Illicado

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
