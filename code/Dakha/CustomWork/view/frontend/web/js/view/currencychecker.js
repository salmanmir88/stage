define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Dakha_CustomWork/js/model/currencychecker'
    ],
    function (Component, additionalValidators, passwordValidation) {
        'use strict';
        additionalValidators.registerValidator(passwordValidation);
        return Component.extend({});
    }
);