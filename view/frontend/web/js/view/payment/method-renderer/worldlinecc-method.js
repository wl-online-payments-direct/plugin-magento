define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success',
    'Worldline_Payment/js/model/device-data',
    'Worldline_Payment/js/model/message-manager',
    'Magento_Ui/js/modal/alert',
], function ($, Component, VaultEnabler, placeOrderAction, redirectOnSuccessAction, deviceData, messageManager, alert) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Worldline_Payment/payment/worldlinecc'
        },

        tokenizer: {},

        /**
         * @returns {exports.initialize}
         */
        initialize: function () {
            this._super();
            this.vaultEnabler = new VaultEnabler();
            this.vaultEnabler.setPaymentCode(this.getVaultCode());
            return this;
        },

        initializeTokenizer: function () {
            let hostedTokenizationPageUrl = window.checkoutConfig.payment[this.getCode()].url;
            this.tokenizer = new Tokenizer(hostedTokenizationPageUrl, 'div-hosted-tokenization', {hideCardholderName: false});

            this.tokenizer.initialize()
                .then(() => {
                    // Do work after initialization, if any
                })
                .catch(reason => {
                    // Handle iFrame load error
                })
        },

        /**
         * Get payment method code
         */
        getCode: function () {
            return this.item.method;
        },

        /**
         * @param {String|null} hostedTokenizationId
         * @returns {Object}
         */
        getData: function (hostedTokenizationId) {
            let data = this._super();

            if (hostedTokenizationId) {
                let additionalData = deviceData.getData();
                additionalData.hosted_tokenization_id = hostedTokenizationId;
                data.additional_data = additionalData;

                this.vaultEnabler.visitAdditionalData(data);
            }

            return data;
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
            return window.checkoutConfig.payment[this.getCode()].ccVaultCode;
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

                this.tokenizer.submitTokenization()
                    .then((result) => {
                        if (result.success) {
                            $.when(
                                placeOrderAction(self.getData(result.hostedTokenizationId), self.messageContainer)
                            ).done(
                                function (orderId) {
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
                                            }
                                        }
                                    )
                                }
                            ).fail(
                                function () {
                                    let msg = $.mage.__('Your payment couldn\'t be completed, please try again');
                                    alert({
                                        content: msg,
                                        actions: {
                                            always: function () {
                                                $('div-hosted-tokenization').empty();
                                                location.reload();
                                            }
                                        }
                                    });
                                }
                            ).always(
                                function () {
                                    self.isPlaceOrderActionAllowed(true);
                                }
                            );

                            return true;
                        }

                        if (result.error) {
                            messageManager.processMessage(result.error.message);
                            self.isPlaceOrderActionAllowed(true);
                        }
                    })
                    .catch((error) => {
                        console.error(error);
                    });

                return true;
            }

            return false;
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
