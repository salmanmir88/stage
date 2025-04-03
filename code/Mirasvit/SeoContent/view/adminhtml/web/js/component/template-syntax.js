define([
    'jquery',
    'underscore',
    'ko',
    'uiComponent'
], function ($, _, ko, Component) {
    'use strict';
    
    return Component.extend({
        hideTimeout: null,
        $wrapper:    null,
        
        defaults: {
            template:      'Mirasvit_SeoContent/component/template-syntax',
            childSelector: '.mst-seo-content__global-template-syntax input, .mst-seo-content__global-template-syntax textarea',
            wrapperClass:  'mst-seo-content__component-template-syntax'
        },
        
        initialize: function () {
            this._super();
            
            var templatesTimer = setInterval(function () {
                if ($('.mst-seo-content__component-template-syntax-wrapper').length
                    && $(this.childSelector).length) {
                    clearInterval(templatesTimer);
                    this.init();
                }
            }.bind(this), 250);
            
            return this;
        },
        
        init: function () {
            var html = $('.mst-seo-content__component-template-syntax-wrapper').html();
            
            this.$wrapper = $('<div/>')
                .addClass(this.wrapperClass)
                .html(html);
            
            $('body').append(this.$wrapper);
            
            _.each($(this.childSelector), function (item) {
                this.attachEvents($(item));
            }.bind(this));
        },
        
        
        attachEvents: function ($item) {
            $item.on('focus', function () {
                clearTimeout(this.hideTimeout);
                
                this.$wrapper.addClass('_visible');
            }.bind(this));
            
            $item.on('blur', function () {
                this.hideTimeout = setTimeout(function () {
                    this.$wrapper.removeClass('_visible');
                }.bind(this), 100);
            }.bind(this));
            
            this.$wrapper.on('click', function () {
                clearTimeout(this.hideTimeout);
            }.bind(this));
            
            $('.close', this.$wrapper).on('click', function () {
                this.$wrapper.removeClass('_visible');
            }.bind(this));
        }
    });
});
