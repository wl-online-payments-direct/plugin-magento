define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success'
], function ($, Component, VaultEnabler, placeOrderAction, redirectOnSuccessAction) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Worldline_Payment/payment/worldlinehc'
        },

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
            return this;
        },

        /**
         * @returns {Boolean}
         */
        isVaultEnabled: function () {
            return this.vaultEnabler.isVaultEnabled();
        },

        /**
         * @returns {String}
         */
        getVaultCode: function () {
            return window.checkoutConfig.payment[this.getCode()].hcVaultCode;
        },

        /**
         * Get list of available CC types
         *
         * @returns {Object}
         */
        getAvailableTypes: function () {
            let availableTypes = window.checkoutConfig.payment[this.getCode()].icons;
            if (availableTypes && availableTypes instanceof Object) {
                return Object.keys(availableTypes);
            }

            return [];
        },

        /**
         * Get payment icons.
         * @param {String} type
         * @returns {Boolean}
         */
        getIcons: function (type) {
            return window.checkoutConfig.payment[this.getCode()].icons.hasOwnProperty(type) ?
                window.checkoutConfig.payment[this.getCode()].icons[type]
                : false;
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
