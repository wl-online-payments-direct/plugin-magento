/*browser:true*/
/*global define*/
define([
    'jquery',
    'Magento_Vault/js/view/payment/method-renderer/vault',
    'Magento_Checkout/js/action/place-order',
    'Magento_Checkout/js/action/redirect-on-success',
    'Worldline_Payment/js/model/device-data',
    'Worldline_Payment/js/model/message-manager'
], function ($, VaultComponent, placeOrderAction, redirectOnSuccessAction, deviceData, messageManager) {
    'use strict';

    return VaultComponent.extend({
        defaults: {
            template: 'Worldline_Payment/payment/vault',
            modules: {
                tokenizer: null
            }
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
                additionalData.public_hash = this.public_hash;
                data.additional_data = additionalData;
            }

            return data;
        },

        /**
         * @returns
         */
        initializeTokenizer: function () {
            let hostedTokenizationPageUrl = window.checkoutConfig.payment.worldline_cc.url;
            this.tokenizer = new Tokenizer(
                hostedTokenizationPageUrl,
                'iframe-' + this.getId(),
                {hideCardholderName: false, hideTokenFields:false},
                this.getToken()
            );
            this.tokenizer.initialize()
                .then(() => {
                    // Do work after initialization, if any
                })
                .catch(reason => {
                    // Handle iFrame load error
                })

            return true;
        },

        /**
         * @returns {String}
         */
        getToken: function () {
            return this.token;
        },

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
                                            }
                                        }
                                    )
                                }
                            ).fail(
                                function () {}
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
