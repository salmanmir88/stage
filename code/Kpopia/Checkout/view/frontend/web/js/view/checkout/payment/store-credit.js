define([
    'jquery',
    'underscore',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Magento_Customer/js/model/customer',
    'Magento_Catalog/js/price-utils',
    'Amasty_StoreCredit/js/action/apply-store-credit',
    'Amasty_StoreCredit/js/action/cancel-store-credit',
    'Amasty_StoreCredit/vendor/tooltipster/js/tooltipster.min'
], function (
    $,
    _,
    Component,
    quote,
    customer,
    priceUtils,
    applyStoreCredit,
    cancelStoreCredit,
    tooltipster
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Kpopia_Checkout/checkout/payment/storecredit',
            isVisible: window.checkoutConfig.amastyStoreCredit.isVisible,
            available: window.checkoutConfig.amastyStoreCredit.amStoreCreditAmountAvailable,
            amount: window.checkoutConfig.amastyStoreCredit.amStoreCreditAmount,
            appliedAmount: 0,
            isApplied: !!window.checkoutConfig.amastyStoreCredit.amStoreCreditUsed,
            isCreditsTooltipEnabled: window.checkoutConfig.amastyStoreCredit.isTooltipEnabled,
            creditsTooltipContent: window.checkoutConfig.amastyStoreCredit.tooltipText,
            isEncourage: window.checkoutConfig.amastyStoreCredit.encourage,
            selectors: {
                tooltipElement: '[data-amcredits-js="tooltip"]'
            }
        },

        initialize: function () {
            this._super();

            this.initTooltip();

            return this;
        },

        initObservable: function () {
            if (this.isApplied) {
                this.available -= this.amount;
            }

            this.appliedAmount = parseFloat(this.amount);

            this._super();
            this.observe(['isVisible', 'available', 'isApplied', 'amount']);

            return this;
        },
        getFormatAmount: function () {
            return priceUtils.formatPrice(this.amount(), quote.getPriceFormat(), false);
        },
        getStoreCreditLeft: function () {
            return priceUtils.formatPrice(this.available(), quote.getPriceFormat(), false);
        },
        applyStoreCredit: function () {
            applyStoreCredit(String(this.amount()))
                .done(function (response) {
                    this.available(this.available() - parseFloat(response));
                    this.amount(response);
                    this.appliedAmount = this.amount();
                    this.isApplied(true);
                }.bind(this))
                .fail(function () {

                });
        },
        cancelStoreCredit: function () {
            cancelStoreCredit()
                .done(function (response) {
                    this.available(this.available() + this.appliedAmount);
                    this.amount(response);
                    this.isApplied(false);
                }.bind(this));
        },

        initTooltip: function () {
            var tooltipTrigger = this.isTouchDevice() ? 'click' : 'hover';

            if (!this.isCreditsTooltipEnabled) {
                return;
            }

            $.async(this.selectors.tooltipElement, function () {
                $(this.selectors.tooltipElement).tooltipster({
                    position: 'right',
                    contentAsHtml: true,
                    interactive: true,
                    trigger: tooltipTrigger
                });
            }.bind(this));
        },

        isTouchDevice: function () {
            return ('ontouchstart' in window)
                || (navigator.maxTouchPoints > 0)
                || (navigator.msMaxTouchPoints > 0);
        }
    });
});
