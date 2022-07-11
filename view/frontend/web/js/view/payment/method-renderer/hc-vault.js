/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success'
], function ($, VaultComponent, placeOrderAction, redirectOnSuccessAction) {
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

            if (this.validate() &&
                this.isPlaceOrderActionAllowed() === true
            ) {
                this.isPlaceOrderActionAllowed(false);

                $.when(
                    placeOrderAction(self.getData(), self.messageContainer)
                ).done(
                    function(orderId) {
                        $.when(
                            self.checkRedirect(orderId)
                        ).done(
                            function (data) {
                                if (!data || (data.url == null)) {
                                    self.afterPlaceOrder.bind(self);
                                    if (self.redirectAfterPlaceOrder) {
                                        redirectOnSuccessAction.execute();
                                    }
                                } else {
                                    window.location.replace(data.url);
                                    return false;
                                }
                            }
                        )
                    }
                ).fail(
                    function () {
                    }
                ).always(
                    function () {
                        self.isPlaceOrderActionAllowed(true);
                    }
                );

                return true;
            }
        },

        checkRedirect: function (orderId) {
            return $.ajax({
                method: "GET",
                url: "/worldline/payment/redirect",
                contentType: "application/json",
                data: {
                    id: orderId
                },
            })
        }
    });
});
