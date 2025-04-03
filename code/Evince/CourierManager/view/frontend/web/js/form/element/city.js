/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    /*'underscore',*/
    'Magento_Ui/js/form/element/abstract',
    'jquery/ui',
    'Evince_CourierManager/js/select2',
    'Evince_CourierManager/js/select2.min'
], function (_, registry, Select, defaultPostCodeResolver,  $) {
    'use strict';

    return Select.extend({
        defaults: {
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.country_id:value'
            }
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            //var string = '[{"name": "Medellin", "code": "50011100"},{"name": "Cali", "code": "50011122"},{"name": "Bogota", "code": "50011133"}]';
            //var options1 = JSON.parse(string);
            //var cityOptions = [];
            console.log('called update closed filter by');
            /*console.log('value = '+value);*/

            var options;
            var cityOptions = [];
            var link = $('input[name="cityurl"]').val();

            $.ajax({
                url: link,
                data:{country_code:value},
                contentType: "application/json",
                async: false,
                success: function (response) {
                    options = response;
                },
                error: function (xhr, ajaxOptions, thrownError) {
                    console.log("There has been an error retrieving the values from the database.");
                }
            });
            
            var opt = JSON.parse(options);
            $.each(opt, function (index, cityOptionValue) {
                    var name = cityOptionValue.name;
                    var valuelabel = cityOptionValue.code;
                    
                    var jsonObject = {
                        value: valuelabel,
                        title: name,
                        country_id: "",
                        label: name
                    };
                    cityOptions.push(jsonObject);
                
            });
            jQuery('.select').select2();
            //console.log('city options'+cityOptions);
            //setTimeout(function(){ console.log('set timeout called'); console.log(this.setOptions(cityOptions));}, 2000);
            /*console.log(cityOptions);*/
            this.setOptions(cityOptions);       
        }
    });
});

