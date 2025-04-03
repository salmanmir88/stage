/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/* Theme JS */
require([
    'jquery',
    'mage/cookies',
    'domReady!'
], function ($) {
    'use strict';
    // scroll to and open tabs
    $('body').on('click', '.js-tab-link', function(e){
        e.preventDefault();
        let anchor = $(this).attr('href');
        if(!anchor && !$(anchor).length && anchor !== '#'){
            return;
        }
        if ($('.product.data.items '+ anchor +'[data-role="content"]').length) {
            $('.product.data.items [data-role="content"]').each(function (index) {
                if (this.id == anchor.substring(1)) {
                    let $wrap = $(this).closest('.product.data.items');
                    let tab = $wrap.data('mageAccordion') || $wrap.data('mage-Accordion') || $wrap.data('mageTabs');
                    if (tab && typeof tab.activate === 'function') {
                        tab.activate(index); 
                    }
                }
            });
        }
        $('html, body').animate({
            scrollTop: $(anchor).offset().top - 200
        }, 200);
    });
    /* dynamic input size for overlay search */
    $.fn.textWidth = function (text, font) {
        if (!$.fn.textWidth.fakeEl) $.fn.textWidth.fakeEl = $('<span>').hide().appendTo(document.body);
        $.fn.textWidth.fakeEl.text(text || this.val() || this.text() || this.attr('placeholder')).css({
            'font': font || this.css('font'),
            'text-transform': 'uppercase',
            'letter-spacing': this.css('letter-spacing')
        });
        return $.fn.textWidth.fakeEl.width();
    };
    $('.width-dynamic').on('input', function () {
        let inputWidth = $(this).textWidth() + 40; //40 is padding
        $(this).css({
            width: inputWidth
        })
    }).trigger('input');
    $('.width-dynamic').on('blur',function () {
        if (!$(this).val().length) {
            $(this).css({
                width: ''
            })
        }
    });

    /* qty */
    const qtyD = {
        min: 0,
        max: 10000000,
        i: 1
    };
    let qtyMin= qtyD.min,
        qtyMax= qtyD.max,
        qtyIncrements= qtyD.i;
    function qtyValidate(_input){
        let data = _input.data('validate');
        qtyMin = qtyD.min;
        qtyMax = qtyD.max;
        qtyIncrements = qtyD.i;
        if (data?.['validate-item-quantity']) {
            qtyIncrements = data['validate-item-quantity']['qtyIncrements'] ?? qtyD.i;
            qtyMax = data['validate-item-quantity']['maxAllowed'] ?? qtyD.max;
        }
        qtyMin = _input.attr('min') ?? qtyD.min; 
    }
    /* input plus minus */
    $("body").on('click', ".qty-controls-wrap .qty-plus", function (event) {
        event.preventDefault();
        const _input = $(this).parent().find('input');
        if(_input.length){
            qtyValidate(_input);
            _input.val(parseInt(_input.val()) + qtyIncrements);
            _input.change();
            $(this).parent().find('button.update-cart-item').show();
        }
    });

    $("body").on('click', ".qty-controls-wrap .qty-minus", function (event) {
        event.preventDefault();
        const _input = $(this).parent().find('input'); 
        if(_input.length){
            qtyValidate(_input);
            let count = parseInt(_input.val()) - qtyIncrements;
            count = count < 0 ? qtyMin : count;
            _input.val(count).change();
            $(this).parent().find('button.update-cart-item').show();
        }
    });
       
    $("body").on('change', ".qty-controls-wrap input", function () {
        const $this = $(this);
        qtyValidate($this);
        let val = parseInt($this.val(), 10);
        val = Math.min(val, qtyMax);
        val = Math.max(val, qtyMin);
        if (isNaN(val)) {
            val = qtyMin;
        }
        $this.val(Math.round(val / qtyIncrements) * qtyIncrements);
        $this.trigger('keypress');
    });


    /* Filter toggle */
    const item = $('.block.filter, .filters-slideout-content').find('.filter-options, .filter-current');

    if (item.length) {
        $(document).on('click', '.filter-options-title, .filter-current-subtitle', function (e) {
            e.preventDefault();
            const speed = 300,
                thisParent = $(this).parent(),
                nextLevel = $(this).next('.filter-options-content, .ox-toggle-content');
            if (thisParent.hasClass('collapsible')) {
                if (thisParent.hasClass('open')) {
                    thisParent.removeClass('open');
                    nextLevel.slideUp(speed, function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                } else {
                    thisParent.addClass('open');
                    nextLevel.slideDown(speed, function () {
                        $('body').trigger('oxToggleUpdated');
                    });
                }
            }
        })
    }

    $.fn.ox_toggles = function () {
        $(this).on('click', '.ox-toggle-title', function (e) {
            e.preventDefault();
            const $title = $(this),
                $parentToggle = $title.parent();
            if ($parentToggle.hasClass('collapsible')) {
                let isOpen = !$parentToggle.hasClass('open'); // Toggle open state
                $parentToggle.toggleClass('open', isOpen).attr('aria-expanded', isOpen); // Toggle open class and aria-expanded
                const $content = $title.next('.ox-toggle-content');
                $content.attr('aria-hidden', !isOpen).css('max-height', isOpen ? $content[0].scrollHeight : 0);
                setTimeout(function () {
                    $('body').trigger('oxToggleUpdated');
                }, 300);
            }
        });
    };    
    $('.ox-toggle').ox_toggles();

    /* Collapsible text */
    $('body').off('click.OXExpand').on('click.OXExpand', '.ox-expand .ox-expand__link', function (e) {
        e.preventDefault();
        const $this = $(this).closest('.ox-expand').eq(0);
        let max_height = $this.data('max-height') || 90,
            isMin = $this.hasClass('minimized');
        $this.attr('aria-expanded', !isMin)
        $this.toggleClass('minimized', !isMin)
        .children('.ox-expand__inner')
        .attr("aria-hidden", isMin)
        .css('max-height', (isMin ? '100%' : parseInt(max_height) + 'px'));
    });
    $(function () {
        $('.ox-expand').each(function () {
            const $this = $(this),
                $inner = $this.find('.ox-expand__inner');
            let max_height = $this.data('max-height') || 90;
            if (parseInt($inner.css('height')) < max_height) {
                let isMin = true;
                $this.attr('aria-expanded', !isMin)
                $this.toggleClass('minimized', !isMin)
                .children('.ox-expand__inner')
                .attr("aria-hidden", isMin)
                .css('max-height', (isMin ? '100%' : parseInt(max_height) + 'px'));
                $this.find('.ox-expand__link').hide();
            }
        });
    });
});

require(['jquery', 'matchMedia'], function ($) {
    $(function () {
        const _this = '.header__item-account-links',
            _class = 'store.links',
            mediaBreakpoint = '(min-width: 1025px)';
        if (_this.length) {
            mediaCheck({
                media: mediaBreakpoint,
                entry: function () {
                    const $this = $(_this),
                        $desktop_parent = $('[data-move-desktop="header.myaccount"]').eq(0),
                        position = $this.data('moveDesktopPosition') || 0;
                    if (!$desktop_parent.length || $this.parent().is($desktop_parent)) {
                        return;
                    }

                    const element = $this.detach();
                    if (0 < position) {
                        const prev = $desktop_parent.children().eq(position - 1);
                        if (prev.length) {
                            prev.after(element);
                        } else {
                            $desktop_parent.append(element);
                        }
                    } else {
                        $desktop_parent.append(element);
                    }
                },
                exit: function () {
                    const $this = $(_this),
                        $mobile_parent = $('[data-move-mobile="' + _class + '"]').eq(0);
                    if (!$this.length || !$mobile_parent.length || $this.parent().is($mobile_parent)) {
                        return;
                    }
                    $this.data('moveDesktopPosition', $this.parent().children().index($this));
                    const element = $this.detach();
                    $mobile_parent.append(element);
                }
            });
        }
    });
});

/* resize fullheight menu if its width bigger than the header area */
require(["jquery", "domReady!"], function ($) {
    "use strict";
    $(function () {
        const body = document.body;
            if (!body.matches('.menu-style-2,.menu-style-4,.menu-style-5')){
                return;
            }
        const headerContent = $('.page-header'),
            headerLeft = headerContent.find('.ox-megamenu-navigation');
        const calcMenuWidth = function () {
            if (headerLeft.length && window.innerWidth > 1024) {
                let count = 0,
                    padding = body.matches('.menu-style-5') ? 24 : 0;
                headerLeft.find("> li").each(function () {
                    count += $(this).outerWidth(true);
                });
                $('body').toggleClass('ox-mm-resize', (headerLeft.innerWidth() - padding) < count);
            }
        };
        calcMenuWidth();
        $(window).on('resize', function () {
            requestAnimationFrame(calcMenuWidth);
        });
    });
});

require(["jquery"], function ($) {
    "use strict";
    $(function () {
        function searchInputFocus() {
            $(".js-input-focus").each(function () {
                if ($(this).val().length) {                    
                    searchInputFocusTrigger(this);
                }
            });
        }
        searchInputFocus();
        $('body').on('modal_opened.OXmodal', function(){
            const $searchModal = $(".ox-slideout-top ");
            if($searchModal.length){
                if ($searchModal.hasClass('active')) {
                    searchInputFocus();
                }
            }
        });
        function searchInputFocusTrigger(element) {
           $(element).closest(".control").addClass('input-focused');
            $("html").addClass('ox-search-focused');
        }
        $(document).on('focus', '.js-input-focus',  function(){
            searchInputFocusTrigger(this);
        }).on('blur', '.js-input-focus', function () {
            if (!$(this).val().length) {
                $(this).closest(".control").removeClass('input-focused');
                $("html").removeClass('ox-search-focused');

            }
        })

    });
});

require(["jquery"], function ($) {
    "use strict";
    $(function () {
        //check device type
        //touch on IOS and Android
        const isAndroid = /(android)/i.test(navigator.userAgent);
        const isMobile = /(mobile)/i.test(navigator.userAgent);
        if (/(iPod|iPhone|iPad)/.test(navigator.userAgent) || isAndroid || isMobile){
            $('html').addClass('touch').removeClass('no-touch');
        } else {
            $('html').addClass('no-touch').removeClass('touch');
        }
    });
});

(() => {
    const toTop = document.getElementById("toTop");
    if (!toTop) return;

    let ticking = false;

    function updateVisibility() {
        if (window.scrollY > 100) {
            toTop.style.visibility = "visible";
            toTop.style.opacity = "1";
        } else {
            toTop.style.opacity = "0";
            setTimeout(() => {
                if (window.scrollY <= 100) {
                    toTop.style.visibility = "hidden";
                }
            }, 300);
        }
        ticking = false;
    }

    window.addEventListener('scroll', function () {
        if (!ticking) {
            requestAnimationFrame(updateVisibility);
            ticking = true;
        }
    }, { passive: true });

    toTop.addEventListener("click", function () {
        window.scrollTo({ top: 0, behavior: "smooth" });
    });

})();