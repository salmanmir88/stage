define([
    'jquery',
    'mage/translate',
    'jquery/ui',
], function ($, $t) {
    'use strict'

    $.widget('tigren.ajaxRemove', {
        options: {
            ajaxRemove: {
                addedWishlistBtnSelector: '[class=added-in-wishlist]',
            }
        },

        _create: function () {
            this._bindSubmit();
        },

        _bindSubmit: function () {

        }
    });
    return $.tigren.ajaxRemove;
});