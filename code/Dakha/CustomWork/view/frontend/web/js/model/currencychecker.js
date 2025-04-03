define(
  [
     'jquery',
     'Magento_Customer/js/model/customer',
     'Magento_Checkout/js/model/quote',
     'Magento_Ui/js/modal/modal',
     'mage/validation'
  ],
    function ($,customer,quote,modal) {
        'use strict';
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: $.mage.__('Error : Selected country saudi arabia'),
            buttons: [{
              text: $.mage.__('Close'),
              class: '',
              click: function () {
                 this.closeModal();
                }
            }]
         };

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                if(quote.shippingAddress()['countryId']=='SA' && window.checkoutConfig.quoteData.quote_currency_code!='SAR')
                  {
                    var popup = modal(options, $('.currency-message'));
                    $('.currency-message').modal('openModal');
                    return false;
                  }
            }
        };
    }
);