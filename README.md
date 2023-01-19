# Worldline Online Payments

## Main Extension for Adobe Commerce with all worldline solutions 

[![M2 Coding Standard](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/coding-standard.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/coding-standard.yml)
[![M2 Mess Detector](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/mess-detector.yml/badge.svg?branch=develop)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments/actions/workflows/mess-detector.yml)

This is a main module that are used as a container to get all Worldline payment solutions:
- [credit card](https://github.com/wl-online-payments-direct/plugin-magento-creditcard)
- [hosted checkout](https://github.com/wl-online-payments-direct/plugin-magento-hostedcheckout)
- [redirect payments (single payment buttons)](https://github.com/wl-online-payments-direct/plugin-magento-redirect-payments)

To install these solutions, you may use
[adobe commerce marketplace](https://marketplace.magento.com/worldline-module-magento-payment.html)
or install them from the github.

Suggested packages:
- [recurring payments](https://github.com/wl-online-payments-direct/plugin-magento-recurring-payments)

### Change log:

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
- Bug fixes for Redirect Payments (single payment button)
- GraphQl improvements and support for Redirect Payments (single payment button)

#### 2.3.0
- Improve configuration settings for Redirect Payments (single payment button)
- Add new payment product Oney for Redirect Payments (single payment button)
- Option added to enforce Strong Customer Authentication for every 3DS request
- Improved design of general settings page
- General code improvements and bug fixes
- Improvements and support for 2.3.x Magento versions
- Support the 4.5.0 version of the Worldline SDK

#### 2.2.3
- Improve work for multi website instances

#### 2.2.2
- Improve the "waiting" page
- Add the "pending" page

#### 2.2.1
- Fix cron run time to prevent order duplication

#### 2.2.0
- Support recurring payments based on Amasty recurring payment extension
- Improve waiting page by adding an order summary block so that customers will always see what they have bought
- Improve payment info block within Magento backend. Merchants can now manually refresh the info available to be sure it is always up to date
- General improvements and bug fixes

#### 2.1.1
- Hide the checkbox "save card" for iFrame checkout (Credit Card payment method) for guests and when the vault is disabled
- Support version 4.3.3 of Worldline SDK
- PWA improvements and support
- Bug fixes and general code improvements

#### 2.1.0
- New Redirect payments: integrate single payment buttons directly on Magento checkout
- Waiting page has been added after payment is done to correctly process webhooks and create the order
- Asyncronic order creation through get calls when webhooks suffer delay
- Refund flow is improved for multi-website instances
- Bancontact payment method implementation has been improved
- General improvements and bug fixes

#### 2.0.0
- Module segregation
- Bug fix and general improvements

#### 1.3.1
- GraphQL bug fix

#### 1.3.0
- Credit card payment method improvements
- New flow for the refunds: pending, refunded statuses for the refund items
- Support Sepa methods for the hosted checkout payment method
- Bug fix and general improvements

#### 1.2.0
- Hosted checkout payment method improvements
- Show Worldline information in order overview pages: frontend and backend
- Add the Worldline request logs grid
- Compatibility with Magento 2.3.7
- Bug fix and general improvements

#### 1.1.0
- PWA support
- Log Worldline requests feature
- Support of new version of the SDK
- Compatibility with Magento 2.4.4
- Bug fix

#### 1.0.0
- Initial MVP version
