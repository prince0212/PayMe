define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/checkout-data',
        'mage/url'
    ],
    function ($, 
        Component,
        selectPaymentMethodAction,
        checkoutData,
        url
    ) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Deloitte_PayMe/payment/form',
            },

            getCode: function() {
                return 'payme';
            },
            
            getLogo: function() {
                return window.checkoutConfig.payment.payme.logo;
            },
            
            selectPaymentMethod: function() {
                selectPaymentMethodAction(this.getData());
                checkoutData.setSelectedPaymentMethod(this.item.method);
                return true;
            },
            
            placeOrder: function (data, event) {
                window.location.replace(url.build('payme/index/index/'));
            }
            
        });
    }
);