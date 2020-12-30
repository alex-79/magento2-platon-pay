
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'mage/url'
    ],
    function (Component,urlBuilder) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Platon_PlatonPay/payment/form',
                transactionResult: '',
                redirectAfterPlaceOrder: false
            },

            initObservable: function () {

                this._super()
                    .observe([
                        'transactionResult'
                    ]);
                return this;
            },

            getCode: function() {
                return 'platon_pay';
            },

            getData: function() {
                return {
                    'method': this.item.method,
                };
            },

            getPolicy: function() {
                return window.checkoutConfig.payment.platon_pay.policy;
            },

            getTransactionResults: function() {
                return _.map(window.checkoutConfig.payment.platon_pay.transactionResults, function(value, key) {
                    return {
                        'value': key,
                        'transaction_result': value
                    }
                });
            },
            afterPlaceOrder: function (data,event) {
                window.location.replace(urlBuilder.build('platon_platon_pay/redirect/index'))
            }
        });
    }
);
