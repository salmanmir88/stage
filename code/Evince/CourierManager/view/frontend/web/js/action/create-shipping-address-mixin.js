define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {


        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            var address = quote.shippingAddress();

            if (address !== null) {
                var country = $("#shipping-new-address-form [name='country_id'] option:selected").text(),
                    city = $("#shipping-new-address-form [name='city'] option:selected").text();

                address.country_id = country;
                address.city = city;
            }

            return originalAction(messageContainer);
        });
    };
});