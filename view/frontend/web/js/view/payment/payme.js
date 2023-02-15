define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'payme',
                component: 'Deloitte_PayMe/js/view/payment/method-renderer/payme'
            }
        );
        return Component.extend({});
    }
);
