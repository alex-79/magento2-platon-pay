
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
                type: 'platon_pay',
                component: 'Platon_PlatonPay/js/view/payment/method-renderer/platon_pay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
