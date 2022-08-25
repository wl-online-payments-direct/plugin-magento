define([
    'jquery',
    'Magento_Checkout/js/view/payment/default',
    'Magento_Vault/js/view/payment/vault-enabler',
    'Worldline_Payment/js/view/credit-card/create-payment',
    'Worldline_Payment/js/model/device-data',
    'Worldline_Payment/js/model/message-manager',
    'Magento_Ui/js/modal/alert',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/url'
], function (
    $,
    Component,
    VaultEnabler,
    placeOrderAction,
    deviceData,
    messageManager,
    alert,
    fullScreenLoader,
    urlBuilder
) {
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

            if (!this.validate() || this.isPlaceOrderActionAllowed() !== true) {
                return false;
            }

            this.isPlaceOrderActionAllowed(false);

            this.tokenizer.submitTokenization()
                .then((result) => {
                    if (result.success) {
                        self.createPayment(result);
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
        },

        createPayment: function (result) {
            let self = this;

            $.when(
                placeOrderAction(this.getData(result.hostedTokenizationId), this.messageContainer)
            ).done(
                function (returnUrl) {
                    if (returnUrl) {
                        window.location.replace(returnUrl);
                    } else {
                        fullScreenLoader.startLoader();
                        setTimeout(() => {
                            self.redirectToSuccess(result.hostedTokenizationId);
                        }, 3000)
                    }
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
        },

        redirectToSuccess: function (hostedTokenizationId) {
            return $.ajax({
                method: "GET",
                url: urlBuilder.build("worldline/CreditCard/ReturnUrl"),
                contentType: "application/json",
                data: {
                    hosted_tokenization_id: hostedTokenizationId
                },
            }).done($.proxy(function (data) {
                if (data.url) {
                    window.location.replace(data.url);
                }
            }, this));
        }
    });
});
