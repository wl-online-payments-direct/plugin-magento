define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    let config = window.checkoutConfig.payment,
        ccType = 'worldline_cc';

    if (config[ccType].isActive) {
        rendererList.push(
            {
                type: ccType,
                component: 'Worldline_Payment/js/view/credit-card/worldlinecc-method'
            }
        );
    }

    return Component.extend({});
});
