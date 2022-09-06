define([
    'uiComponent',
    'Magento_Checkout/js/model/payment/renderer-list'
], function (Component, rendererList) {
    'use strict';

    let config = window.checkoutConfig.payment,
        hcType = 'worldline_hosted_checkout';

    if (config[hcType].isActive) {
        rendererList.push(
            {
                type: hcType,
                component: 'Worldline_Payment/js/view/hosted-checkout/worldlinehc-method'
            }
        );
    }

    return Component.extend({});
});
