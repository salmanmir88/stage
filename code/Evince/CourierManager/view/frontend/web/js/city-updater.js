define([
    'jquery',
    'mage/template',
    'underscore',
    'jquery/ui',
    'mage/validation',
    'Magento_Checkout/js/region-updater'
], function ($, mageTemplate, _) {
    'use strict';
    $.widget('mage.cityUpdater', {
        options: {
            cityTemplate: '<option value="<%- data.value %>" <% if (data.isSelected) { %>selected="selected"<% } %>>' +
                '<%- data.title %>' +
                '</option>',
            isCityRequired: true,
            currentCity: null
        },

        _create: function () {
            this._initCountryElement();
            this._initRegionElement();

            var regionList = $(this.options.regionListId),
                regionInput = $(this.options.regionInputId);

            this.currentCityOption = this.options.currentCity;
            this.cityTmpl = mageTemplate(this.options.cityTemplate);

            if ($(regionList).is(":visible")) {
                console.log('is visible is here');
                console.log($(regionList).find('option:selected').val());
                this._updateCity($(regionList).find('option:selected').val());
            } else {
                this._updateCity(null);
            }

            /*$('#city_id').on('change', function(){
                console.log('changing city');
                var cityValue = $(this).find('option:selected').val();
                var cityNameText = $(this).find('option:selected').text();
                console.log(cityNameText);
                if(cityValue!='')
                {
                    console.log('Setting name for city');
                    console.log(this.options.cityInputId);
                    $(this.options.cityInputId).val(cityNameText);
                }

            });*/

            $(this.options.cityListId).on('change', $.proxy(function (e) {
                    this.setOption = false;
                    this.currentCityOption = $(e.target).val();
                    console.log('change is triggering');
                    console.log($(e.target).val());
                    if ($(e.target).val() != '') {
                        console.log('inside if');
                        console.log($(e.target).find('option:selected').text());
                        if(this.options.cityInputId == undefined)
                        {
                            console.log('undefined wala if');
                           $('#city').val($(e.target).find('option:selected').text()); 
                        }
                        else
                        {
                           console.log('inside else');
                           console.log(this.options.cityInputId);
                           $(this.options.cityInputId).val($(e.target).find('option:selected').text());
                           $(this.options.cityInputId).attr('value',$(e.target).find('option:selected').text());

                        }
                        
                    }
                }, this)
            );

            $(this.options.cityInputId).on('focusout', $.proxy(function () {
                    this.setOption = true;
                }, this)
            );
        },

        _initCountryElement: function () {
            this.element.on('change', $.proxy(function (e) {
                if ($(this.options.regionListId) !== 'undefined'
                    && $(this.options.regionListId).find('option:selected').val() != ''
                ) {
                    this._updateCity($(this.options.regionListId).find('option:selected').val());
                } else {
                    this._updateCity(null);
                }

            }, this));
        },

        _initRegionElement: function () {
            $(this.options.regionListId).on('change', $.proxy(function (e) {
                this._updateCity($(e.target).val());
            }, this));

            $(this.options.regionInputId).on('focusout', $.proxy(function () {
                this._updateCity(null);
            }, this));
        },

        _updateCity: function (regionId) {
            var cityList = $(this.options.cityListId),
                cityInput = $(this.options.cityInputId),
                label = cityList.parent().siblings('label'),
                requiredLabel = cityList.parents('div.field');

            this._clearError();

            // populate city dropdown list if available else use input box
            if (this.options.cityJson[regionId]) {
                this._removeSelectOptions(cityList);
                $.each(this.options.cityJson[regionId], $.proxy(function (key, value) {
                    this._renderSelectOption(cityList, key, value);
                }, this));

                if (this.currentCityOption && cityList.find('option[value="' + this.currentCityOption + '"]').length > 0) {
                    cityList.val(this.currentCityOption);
                }

                if (this.setOption) {
                    cityList.find('option').filter(function () {
                        return this.text === cityInput.val();
                    }).attr('selected', true);
                }

                if (this.options.isCityRequired) {
                    cityList.addClass('required-entry').removeAttr('disabled');
                    requiredLabel.addClass('required');
                } else {
                    cityList.removeClass('required-entry validate-select').removeAttr('data-validate');
                    requiredLabel.removeClass('required');
                }

                cityList.show();
                cityInput.removeClass('required-entry').hide();
                label.attr('for', cityList.attr('id'));
            } else {
                if (this.options.isCityRequired) {
                    cityInput.addClass('required-entry').removeAttr('disabled');
                    requiredLabel.addClass('required');
                } else {
                    requiredLabel.removeClass('required');
                    cityInput.removeClass('required-entry');
                }

                cityList.removeClass('required-entry').prop('disabled', 'disabled').hide();
                cityInput.show();
                label.attr('for', cityInput.attr('id'));
            }
            cityList.attr('defaultvalue', this.options.defaultCityId);
        },

        _removeSelectOptions: function (selectElement) {
            selectElement.find('option').each(function (index) {
                if (index) {
                    $(this).remove();
                }
            });
        },

        _renderSelectOption: function (selectElement, key, value) {
            console.log(value);
            console.log(key);
            console.log(selectElement);
            selectElement.append($.proxy(function () {
                var name = value.name.replace(/[!"#$%&'()*+,.\/:;<=>?@[\\\]^`{|}~]/g, '\\$&'),
                    tmplData,
                    tmpl;

                if (value.code && $(name).is('span')) {
                    key = value.code;
                    value.name = $(name).text();
                }

                tmplData = {
                    value: key,
                    title: value.name,
                    isSelected: false
                };

                if (this.options.defaultCityId === key) {
                    tmplData.isSelected = true;
                }

                tmpl = this.cityTmpl({
                    data: tmplData
                });

                return $(tmpl);
            }, this));
        },

        _clearError: function () {
            var args = ['clearError', this.options.cityListId, this.options.cityInputId];

            if (this.options.clearError && typeof this.options.clearError === 'function') {
                this.options.clearError.call(this);
            } else {
                if (!this.options.form) {
                    this.options.form = this.element.closest('form').length ? $(this.element.closest('form')[0]) : null;
                }

                this.options.form = $(this.options.form);
                this.options.form && this.options.form.data('validator') &&
                this.options.form.validation.apply(this.options.form, _.compact(args));

                // Clean up errors on region & zip fix
                $(this.options.cityInputId).removeClass('mage-error').parent().find('[generated]').remove();
                $(this.options.cityListId).removeClass('mage-error').parent().find('[generated]').remove();
            }
        }
    });
    return $.mage.cityUpdater;
});
