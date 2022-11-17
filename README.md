# Worldline Online Payments

The package includes the following payment methods:
- credit card
- hosted checkout
- redirect payment (single payment buttons)

Suggested packages:
- recurring payments

Change log:

2.2.1
- Fix cron run time to prevent order duplication

2.2.0
- Support recurring payments based on Amasty recurring payment extension
- Improve waiting page by adding an order summary block so that customers will always see what they have bought
- Improve payment info block within Magento backend. Merchants can now manually refresh the info available to be sure it is always up to date
- General improvements and bug fixes

2.1.1
- Hide the checkbox "save card" for iFrame checkout (Credit Card payment method) for guests and when the vault is disabled
- Support version 4.3.3 of Worldline SDK
- PWA improvements and support
- Bug fixes and general code improvements

2.1.0
- New Redirect payments: integrate single payment buttons directly on Magento checkout
- Waiting page has been added after payment is done to correctly process webhooks and create the order
- Asyncronic order creation through get calls when webhooks suffer delay
- Refund flow is improved for multi-website instances
- Bancontact payment method implementation has been improved
- General improvements and bug fixes

2.0.0
- Module segregation
- Bug fix and general improvements

1.3.1
- GraphQL bug fix

1.3.0
- Credit card payment method improvements
- New flow for the refunds: pending, refunded statuses for the refund items
- Support Sepa methods for the hosted checkout payment method
- Bug fix and general improvements

1.2.0
- Hosted checkout payment method improvements
- Show Worldline information in order overview pages: frontend and backend
- Add the Worldline request logs grid
- Compatibility with Magento 2.3.7
- Bug fix and general improvements

1.1.0
- PWA support
- Log Worldline requests feature
- Support of new version of the SDK
- Compatibility with Magento 2.4.4
- Bug fix

1.0.0
- Initial MVP version
