define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert, $t) {
    'use strict';

    return {
        /**
         * @param errorMessage
         */
        processMessage: function (errorMessage) {
            let defaultErrorMessage = $t('Sorry, but something went wrong')

            if (this.useDefaultErrorMessage(errorMessage)) {
                alert({content: defaultErrorMessage});
                return;
            }

            alert({content: errorMessage});
        },

        /**
         * @param errorMessage
         * @returns {boolean}
         */
        useDefaultErrorMessage: function (errorMessage) {
            if (errorMessage === undefined) {
                return true;
            }

            return errorMessage === 'An error occurred: Please check if all fields are correctly filled.';
        }
    }
});
