define([
	'jquery',
	'jquery-ui-modules/widget',
	'jquery-ui-modules/core',
], function ($) {
	'use strict';

	$.widget('ox.OxMegaMenu', {
		options: {
			categoryLayoutClass: 'catalog-product-view',
			btn: false,
			actionActive: 'active',
			toggleTransitionDuration: 400,
			classNavigation: '.ox-megamenu-navigation',
			classDropdown: '.ox-megamenu__dropdown',
			dupParents: false,
		},
		_create: function () {
			var _self = this,
				$ox_megamenu = this.element;
				this.ox_megamenu__navigation = $ox_megamenu.find(this.options.classNavigation);
			this.is_desktop = false;
			this._widthHandlers({
				elem: $ox_megamenu.find('li.parent > .ox-mm-a-wrap, li.ox-dropdown--megamenu > .ox-mm-a-wrap'),
				namespace: 'togglelist',
				desktop: false,
				events: {
					'click': function (e) {
						if (('A' === e.target.tagName || 'SPAN' === e.target.tagName) && $(this).parent().closest('li').eq(0).hasClass('ox-megamenu--opened') || !$(this).closest('li').eq(0).find('.submenu, .ox-submenu, .ox-megamenu__dropdown').length)
							return;
						_self._toggleList(this);

						e.preventDefault();
						return false;
					},
				}
			});
			this._duplicateParents();
			this._setActiveMenu();
			$(this.window).trigger('resize');
		},
		_duplicateParents: function () {
			if(!this.options.dupParents){
				return;
			}
			/* duplicate top level items */
			var subMenus = this.element.find('.level-top');
			$.each(subMenus, $.proxy(function (index, item) {
				var linkWrapper = $(item).find('> .ox-mm-a-wrap');
				if(!linkWrapper.length) return;
				var category = $(linkWrapper).find('a .name').text(),
					categoryUrl = $(linkWrapper).find('a').attr('href'),
					menu = $(item).find('> .ox-megamenu__dropdown');

				var categoryLink = $('<a>')
					.attr('href', categoryUrl)
					.text($.mage.__('All %1').replace('%1', category));

				var categoryParent = $('<li>')
					.addClass('level1 category-item hide-on-desktop all-category')
					.html(categoryLink);
				var newLinkWrapper = $('<div>').addClass('ox-mm-a-wrap').html(categoryParent);
				if (menu.find('.all-category').length === 0 && categoryUrl !== '#' && !item.hasClass('nolink')) {
					$(menu).find('.ox-megamenu-list').prepend(newLinkWrapper);
				}
			}, this));
		},
		_toggleList: function (btn) {
			var _self = this,
				$btn = $(btn),
				$li = $btn.parent(),
				$lvl = $li.find('> *:not(.ox-mm-a-wrap)');
				$lvl.css('height', '');
				$lvl.stop();
			if ($li.hasClass('ox-megamenu--opened')) {
				var $li_other = $li.find('.ox-megamenu--opened');

				$li.removeClass('ox-megamenu--opened');
				$lvl.animate({
					height: "toggle"
					}, _self.options.toggleTransitionDuration, function() {
						$li_other.removeClass('ox-megamenu--opened').removeAttr('style');
						$li_other.find('ul').removeAttr('style');
					});
			} else {
				$lvl.animate({
					height: "toggle"
					}, _self.options.toggleTransitionDuration);
				$li.addClass('ox-megamenu--opened');
				$('body').trigger('contentUpdated');
			}
		},
		_widthHandlers: function (obj) {
			var _self = this;
			
			$(window).on('resize.handlerswidth', function () {

				if (obj.desktopBp === _self.is_desktop)
					return; 
				else
					obj.desktopBp = _self.is_desktop; 

				var $elem = $(obj.elem),
					ns = obj.namespace ? '.' + obj.namespace : '';

				if (obj.desktop === _self.is_desktop) {
					$.each(obj.events, function (key, val) {
						$elem.on(key + ns, obj.delegate, val);
					});
				} else {
					$.each(obj.events, function (key) {
						$elem.off(key + ns);
					});
				}
			});
		},
		/**
         * Tries to figure out the active category for current page and add appropriate classes:
         *  - 'active' class for active category
         *  - 'has-active' class for all parents of active category
         *
         *  First, checks whether current URL is URL of category page,
         *  otherwise tries to retrieve category URL in case of current URL is product view page URL
         *  which has category tree path in it.
         *
         * @return void
         * @private
         */
		_setActiveMenu: function () {
			var currentUrl = window.location.href.split('?')[0];

			if (!this._setActiveMenuForCategory(currentUrl)) {
				this._setActiveMenuForProduct(currentUrl);
			}
		},

		/**
		 * Looks for category with provided URL and adds 'active' CSS class to it if it was not set before.
		 * If menu item has parent categories, sets 'has-active' class to all af them.
		 *
		 * @param {String} url - possible category URL
		 * @returns {Boolean} - true if active category was founded by provided URL, otherwise return false
		 * @private
		 */
		_setActiveMenuForCategory: function (url) {
			const activeCategoryLink = this.ox_megamenu__navigation.find('a[href="' + url + '"]');

			if (!activeCategoryLink || !activeCategoryLink.parent().hasClass('ox-mm-a-wrap')) {
				//category was not found by provided URL
				return false;
			} else {
				const $menuItem = activeCategoryLink.parent().parent();
				if(!$menuItem.hasClass('active')) {
					$menuItem.addClass('active');
					// go up until menu wrapper and add has-active to all parent items
					$menuItem.parentsUntil(this.options.classNavigation).filter('.category-item.parent').not('.ox-dd--all').addClass('has-active');
				}
			}

			return true;
		},
		/**
		 * Extracts the URL extension from the given URL.
		 * It identifies the last segment of the URL after the last slash ('/') and returns the substring after the last dot ('.')
		 * If there's no dot in the last segment, it returns an empty string.
		 *
		 * @param {String} url - The URL from which to extract the extension.
		 * @return {String} The extracted URL extension or an empty string if no extension is found.
		 * @private
		 */
		_getUrlExtension: function (url) {
			var lastSegment = url.slice(url.lastIndexOf('/') + 1);
			return lastSegment.includes('.') ? lastSegment.slice(lastSegment.lastIndexOf('.')) : '';
		},

		/**
		 * Determines if the current page is a product page.
		 * It checks the catalog product view related class in the body tag of the document.
		 *
		 * @return {Boolean} True if the current page is a product page, false otherwise.
		 * @private
		 */
		_isProductPage: function () {
			return document.body.classList.contains(this.options.categoryLayoutClass);
		},

		/**
		 * Sets the active state in the menu for a product page. Determines the category URL from either
		 * the referrer URL or the current URL, using the URL extension to identify the category.
		 * Sets the corresponding category as active in the menu if a valid category URL is found.
		 * Clears the active state if no valid category URL is found or if it's not a product page.
		 *
		 * @param {String} currentUrl - The current page URL without parameters.
		 * @return void
		 * @private
		 */
		_setActiveMenuForProduct: function (currentUrl) {
			var firstCategoryUrl = this.ox_megamenu__navigation.find('> li a').attr('href');

			if (!firstCategoryUrl) {
				this._clearActiveState();
				return;
			}

			var categoryUrlExtension = this._getUrlExtension(firstCategoryUrl);
			var categoryUrl;
			var isProductPage = this._isProductPage();

			if (isProductPage) {
				var currentHostname = window.location.hostname;

				if (document.referrer.includes(currentHostname) && document.referrer.endsWith(categoryUrlExtension)) {
					categoryUrl = document.referrer.split('?')[0];
				} else {
					categoryUrl = currentUrl.substring(0, currentUrl.lastIndexOf('/')) + categoryUrlExtension;
				}

				this._setActiveMenuForCategory(categoryUrl);
			} else {
				this._clearActiveState();
			}
		},

		/**
		 * Clears the active state from all menu items within the navigation element.
		 * It removes 'active' and 'has-active' classes from all list items (li elements),
		 * which are used to indicate the currently selected or parent of a selected item.
		 *
		 * @return void
		 * @private
		 */
		_clearActiveState: function () {
			this.element.find('li').removeClass('active has-active');
		},
		_setOption: function (key, value, is_attr) {
			if (is_attr && !value)
				return;

			$.Widget.prototype._setOption.apply(this, arguments);
		},
		destroy: function () {
			$.Widget.prototype.destroy.call(this);
		}
	});

	return $.ox.OxMegaMenu;
});
