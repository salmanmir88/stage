define([
    'jquery',
    'underscore',
    'jquery/ui',
    'jquery/jquery.parsequery',
    'Magento_Swatches/js/swatch-renderer'
], function ($, _) {
    'use strict';

    $.widget('tigren.SwatchRenderer', $.mage.SwatchRenderer, {
        /**
         * Render controls
         *
         * @private
         */
        _RenderControls: function () {
            var $widget = this,
                container = this.element,
                classes = this.options.classes,
                chooseText = this.options.jsonConfig.chooseText;

            $widget.optionsMap = {};

            $.each(this.options.jsonConfig.attributes, function () {
                var item = this,
                    options = $widget._RenderSwatchOptions(item),
                    select = $widget._RenderSwatchSelect(item, chooseText),
                    input = $widget._RenderFormInput(item),
                    label = '';

                // Show only swatch controls
                if ($widget.options.onlySwatches && !$widget.options.jsonSwatchConfig.hasOwnProperty(item.id)) {
                    return;
                }

                if ($widget.options.enableControlLabel) {
                    label +=
                        '<span class="' + classes.attributeLabelClass + '">' + item.label + '</span>' +
                        '<span class="' + classes.attributeSelectedOptionLabelClass + '"></span>';
                }

                if ($widget.productForm) {
                    var attributeSelector = '.swatch-attribute ' + '.' + item.code,
                        attributeElm = $widget.productForm.find(attributeSelector);
                    if (attributeElm.length !== 0) {
                        attributeElm.after(input);
                        input = '';
                    }
                }

                // Create new control
                container.append(
                    '<div class="' + classes.attributeClass + ' ' + item.code +
                    '" attribute-code="' + item.code +
                    '" attribute-id="' + item.id + '">' +
                    label +
                    '<div class="' + classes.attributeOptionsWrapper + ' clearfix">' +
                    options + select +
                    '</div>' + input +
                    '</div>'
                );

                $widget.optionsMap[item.id] = {};

                // Aggregate options array to hash (key => value)
                $.each(item.options, function () {
                    if (this.products.length > 0) {
                        $widget.optionsMap[item.id][this.id] = {
                            price: parseInt(
                                $widget.options.jsonConfig.optionPrices[this.products[0]].finalPrice.amount,
                                10
                            ),
                            products: this.products
                        };
                    }
                });
            });

            // Connect Tooltip
            container
                .find('[option-type="1"], [option-type="2"], [option-type="0"], [option-type="3"]')
                .SwatchRendererTooltip();

            // Hide all elements below more button
            $('.' + classes.moreButton).nextAll().hide();

            // Handle events like click or change
            $widget._EventListener();

            // Rewind options
            $widget._Rewind(container);

            //Emulate click on all swatches from Request
            $widget._EmulateSelected($.parseQuery());
            $widget._EmulateSelected($widget._getSelectedAttributes());
        },


        _determineProductData: function () {
            var isInProductView;
            if (this.element.parents('#mb-ajaxsuite-popup-wrapper').length) {

                isInProductView = false;

            }

            return {
                isInProductView: isInProductView
            };
        },
        processUpdateBaseImage: function (images, context, isInProductView, gallery) {
            var justAnImage = images[0],
                initialImages = this.options.mediaGalleryInitial,
                imagesToUpdate,
                isInitial;

            if (isInProductView & !this.element.parents('#mb-ajaxsuite-popup-wrapper').length) {
                imagesToUpdate = images.length ? this._setImageType($.extend(true, [], images)) : [];
                isInitial = _.isEqual(imagesToUpdate, initialImages);

                if (this.options.gallerySwitchStrategy === 'prepend' && !isInitial) {
                    imagesToUpdate = imagesToUpdate.concat(initialImages);
                }

                imagesToUpdate = this._setImageIndex(imagesToUpdate);
                gallery.updateData(imagesToUpdate);

                if (isInitial) {
                    $(this.options.mediaGallerySelector).AddFotoramaVideoEvents();
                } else {
                    $(this.options.mediaGallerySelector).AddFotoramaVideoEvents({
                        selectedOption: this.getProduct(),
                        dataMergeStrategy: this.options.gallerySwitchStrategy
                    });
                }

                gallery.first();

            } else if (justAnImage && justAnImage.img) {
                context.find('.product-image-photo').attr('src', justAnImage.img);
            }
        },
		
		/**
         * Event listener
         *
         * @private
         */
        _EventListener: function () {
            var $widget = this,
                options = this.options.classes,
                target;

            $widget.element.on('click', '.' + options.optionClass, function () {
                return $widget._OnClick($(this), $widget);
            });

            $widget.element.on('change', '.' + options.selectClass, function () {
                return $widget._OnChange($(this), $widget);
            });

            $widget.element.on('click', '.' + options.moreButton, function (e) {
                e.preventDefault();

                return $widget._OnMoreClick($(this));
            });

            $widget.element.on('keydown', function (e) {
                if (e.which === 13) {
                    target = $(e.target);

                    if (target.is('.' + options.optionClass)) {
                        return $widget._OnClick(target, $widget);
                    } else if (target.is('.' + options.selectClass)) {
                        return $widget._OnChange(target, $widget);
                    } else if (target.is('.' + options.moreButton)) {
                        e.preventDefault();

                        return $widget._OnMoreClick(target);
                    }
                }
            });
        },

		
		_OnClick: function ($this, $widget) {
			var $parent = $this.parents('.' + $widget.options.classes.attributeClass),
				$label = $parent.find('.' + $widget.options.classes.attributeSelectedOptionLabelClass),
				attributeId = $parent.attr('attribute-id'),
				$input = $parent.find('.' + $widget.options.classes.attributeInput);

			if ($widget.inProductList) {
				$input = $widget.productForm.find(
					'.' + $widget.options.classes.attributeInput + '[name="super_attribute[' + attributeId + ']"]'
				);
			}

			if ($this.hasClass('disabled')) {
				return;
			}

			if ($this.hasClass('selected')) {
				$parent.removeAttr('option-selected').find('.selected').removeClass('selected');
				$input.val('');
				$label.text('');
			} else {
				$parent.attr('option-selected', $this.attr('option-id')).find('.selected').removeClass('selected');
				$label.text($this.attr('option-label'));
				$input.val($this.attr('option-id'));
				$this.addClass('selected');
				console.log($this.attr('option-id')); // Here you can find selected simple product id
			}

			$widget._Rebuild();

			if ($widget.element.parents($widget.options.selectorProduct)
					.find(this.options.selectorProductPrice).is(':data(mage-priceBox)')
			) {
				$widget._UpdatePrice();
			}

			$widget._LoadProductMedia();
			$input.trigger('change');
		},
    });

    return $.tigren.SwatchRenderer;
});