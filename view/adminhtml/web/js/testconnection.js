/**
 * @api
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/translate'
], function ($, alert) {
    'use strict';

    $.widget('mage.testConnection', {
        options: {
            url: '',
            elementId: '',
            successText: '',
            failedText: ''
        },

        api_creds: {
            api_key: '',
            api_key_prod: '',
            api_secret: '',
            api_secret_prod: '',
            api_test_endpoint: '',
            api_prod_endpoint: '',
            environment_mode: '',
            merchant_id: '',
            merchant_id_prod: ''
        },

        /**
         * Bind handlers to events
         */
        _create: function () {
            this._on({
                'click': $.proxy(this._connect, this)
            });
            let self = this;
            $.each($.parseJSON(this.options.fieldMapping), function (key, el) {
                $('#' + el).focusout(function () {
                    self._setParams();
                });
            });
        },

        _setParams: function () {
            let self = this;
            $.each($.parseJSON(this.options.fieldMapping), function (key, el) {
                self.api_creds[key] = $('#' + el).val();
            });
        },

        /**
         * Method triggers an AJAX request to check worldline connection
         * @private
         */
        _connect: function () {
            var result = this.options.failedText,
                element = $('#' + this.options.elementId),
                self = this,
                msg = '',
                fieldToCheck = this.options.fieldToCheck || 'success';

            if (!$('#worldline_connection_connection').validate({errorClass: 'mage-error'}).form()) {
                return;
            }

            element.removeClass('success').addClass('fail');
            $.ajax({
                url: this.options.url,
                showLoader: true,
                data: this.api_creds,
                headers: this.options.headers || {}
            }).done(function (response) {
                if (response[fieldToCheck]) {
                    element.removeClass('fail').addClass('success');
                    result = self.options.successText;
                    alert({
                        title: $.mage.__('Success!'),
                        content: $.mage.__('Press OK to save'),
                        actions: {
                            always: function () {
                                $('form#config-edit-form').trigger('save');
                            }
                        }
                    });
                } else {
                    msg = response.errorMessage;
                    if (msg) {
                        alert({
                            content: $.mage.__('Something went wrong. Please check your credentials'),
                            actions: {
                                always: function () {
                                    location.reload();
                                }
                            }
                        });
                    }
                }
            }).always(function () {
                $('#' + self.options.elementId + '_result').text(result);
            });
        }
    });

    return $.mage.testConnection;
});
