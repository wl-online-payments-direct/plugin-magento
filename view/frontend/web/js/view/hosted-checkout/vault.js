/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Worldline_Payment/js/view/hosted-checkout/redirect'
], function ($, fullScreenLoader, VaultComponent, placeOrderAction) {
    'use strict';

    return VaultComponent.extend({
        /**
         * Get last 4 digits of card
         * @returns {String}
         */
        getMaskedCard: function () {
            return this.details.maskedCC;
        },

        /**
         * Get expiration date
         * @returns {String}
         */
        getExpirationDate: function () {
            return this.details.expirationDate;
        },

        /**
         * Get card type
         * @returns {String}
         */
        getCardType: function () {
            return this.details.type;
        },

        getToken: function () {
            return this.public_hash;
        },

        placeOrder: function (data, event) {
            let self = this;

            if (event) {
                event.preventDefault();
            }

            if (!this.validate() ||
                this.isPlaceOrderActionAllowed() !== true
            ) {
                return false;
            }

            fullScreenLoader.startLoader();

            this.isPlaceOrderActionAllowed(false);

            $.when(
                placeOrderAction(self.getData(), self.messageContainer)
            ).done(
                function (redirectUrl) {
                    if (redirectUrl) {
                        window.location.replace(redirectUrl);
                    }
                }
            ).fail(
                function () {
                    self.isPlaceOrderActionAllowed(true);
                }
            ).always(
                function () {
                    fullScreenLoader.stopLoader();
                }
            );

            return true;
        }
    });
});
