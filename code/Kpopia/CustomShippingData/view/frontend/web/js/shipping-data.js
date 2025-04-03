define([
    'uiComponent',
    'jquery',
    'Magento_Checkout/js/model/quote'
], function (Component, $, quote) {
    'use strict';

    return Component.extend({
        initialize: function () {
            this._super();
            var self = this;

            // Load customer data from window.customerData
            var customer = window.customerData || {};

            // Event listener for the Next button click
            $(document).on('click', '[data-role=opc-continue]', function (event) {
                var shippingAddress = quote.shippingAddress();
                if (customer && customer.email) {
                    var customerEmail = customer.email || '';  // Extract email
                    var gender = parseInt(customer.gender, 10) || ''; // Convert gender to integer, default to '' if NaN
                    var customerGender;
                    switch (gender) {
                        case 0:
                            customerGender = '';
                            break;
                        case 1:
                            customerGender = 'Male';
                            break;
                        case 2:
                            customerGender = 'Female';
                            break;
                        case 3:
                            customerGender = 'Not Specified';
                            break;
                        default:
                            customerGender = ''; // Optional: handle unexpected values
                            break;
                    }
                } else {
                    var customerEmail = quote.guestEmail;
                }
                if (shippingAddress) {
                    var shippingData = {
                        firstName: shippingAddress.firstname,
                        lastName: shippingAddress.lastname,
                        street: shippingAddress.street.join(', '),
                        country: shippingAddress.countryId,
                        state: shippingAddress.region,
                        city: shippingAddress.city,
                        postalCode: shippingAddress.postcode,
                        phone: shippingAddress.telephone,
                        customerEmail: customerEmail, // Include the customer email
                        customerGender: customerGender // Include the customer gender
                    };

                    // Save data to localStorage
                    localStorage.setItem('checkoutShippingData', JSON.stringify(shippingData));

                    // Push data to GTM Data Layer
                    window.dataLayer = window.dataLayer || [];
                    window.dataLayer.push({
                        'event': 'shippingInfo',
                        'shippingData': shippingData
                    });
                }
            });
        }
    });
});

