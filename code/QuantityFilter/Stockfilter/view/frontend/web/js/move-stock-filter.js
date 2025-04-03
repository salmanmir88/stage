define(['jquery'], function ($) {
    'use strict';

    function moveInStockFilterUp() {
        var $inStockFilter = $('.filter-options-item[attribute="in-stock"]');
        var $parent = $inStockFilter.parent();

        if ($parent.length) {
            $inStockFilter.prependTo($parent);
            console.log("Moved 'In Stock' filter to the top.");
        } else {
            console.log("'In Stock' filter not found.");
        }
    }

    require(['jquery', 'mage/template'], function ($) {
        $(document).ready(function () {
            // Move filter on page load
            moveInStockFilterUp();

            // Listen for AJAX updates and move filter again
            $(document).on('ajaxComplete layeredAjaxFilterUpdated', function () {
                console.log("AJAX content updated, reordering filters...");
                moveInStockFilterUp();
            });
        });
    });
});
