define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    let config = window.checkoutConfig.payment,
        ccType = 'worldline_cc',
        hcType = 'worldline_hosted_checkout';

    if (config[ccType].isActive) {
        rendererList.push(
            {
                type: ccType,
                component: 'Worldline_Payment/js/view/payment/method-renderer/worldlinecc-method'
            }
        );
    }

    if (config[hcType].isActive) {
        rendererList.push(
            {
                type: hcType,
                component: 'Worldline_Payment/js/view/payment/method-renderer/worldlinehc-method'
            }
        );
    }

    return Component.extend({});
});
