var config = {
    map: {
        '*': {
            cityUpdater: 'Evince_CourierManager/js/city-updater',
            selectize2: 'Evince_CourierManager/js/select2',
            selectize2min: 'Evince_CourierManager/js/select2.min'
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/create-shipping-address': {
                'Evince_CourierManager/js/action/create-shipping-address-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Evince_CourierManager/js/action/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/action/create-billing-address': {
                'Evince_CourierManager/js/action/set-billing-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping-information/list':{
                'Evince_CourierManager/js/view/shipping-information/list-mixin' :true
            },
        }
    }
};