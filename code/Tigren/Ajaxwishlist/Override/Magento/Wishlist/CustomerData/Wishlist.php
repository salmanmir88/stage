<?php

namespace Tigren\Ajaxwishlist\Override\Magento\Wishlist\CustomerData;

use Tigren\Ajaxwishlist\Block\Js;

/**
 * Class Wishlist
 * @package Tigren\Ajaxwishlist\Override\Magento\Wishlist\CustomerData
 */
class Wishlist extends \Magento\Wishlist\CustomerData\Wishlist
{
    /**
     * @var Js
     */
    protected $_customerWishlistItemsNumber;

    /**
     * Wishlist constructor.
     * @param \Magento\Wishlist\Helper\Data $wishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Sidebar $block
     * @param \Magento\Catalog\Helper\ImageFactory $imageHelperFactory
     * @param \Magento\Framework\App\ViewInterface $view
     * @param Js $customerWishlistItemsNumber
     */
    public function __construct(
        \Magento\Wishlist\Helper\Data $wishlistHelper,
        \Magento\Wishlist\Block\Customer\Sidebar $block,
        \Magento\Catalog\Helper\ImageFactory $imageHelperFactory,
        \Magento\Framework\App\ViewInterface $view,
        Js $customerWishlistItemsNumber
    ) {
        $this->_customerWishlistItemsNumber = $customerWishlistItemsNumber;
        parent::__construct($wishlistHelper, $block, $imageHelperFactory, $view);
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getItems()
    {
        $this->view->loadLayout();

        $collection = $this->wishlistHelper->getWishlistItemCollection();
        $collection->clear()->setPageSize($this->_customerWishlistItemsNumber->getProductsNumber())
            ->setInStockFilter(true)->setOrder('added_at');

        $items = [];
        foreach ($collection as $wishlistItem) {
            $items[] = $this->getItemData($wishlistItem);
        }
        return $items;
    }

    /**
     * @param \Magento\Wishlist\Model\Item $wishlistItem
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getItemData(\Magento\Wishlist\Model\Item $wishlistItem)
    {
        $product = $wishlistItem->getProduct();
        return [
            'image' => $this->getImageData($product),
            'product_url' => $this->wishlistHelper->getProductUrl($wishlistItem),
            'product_name' => $product->getName(),
            'product_id' => $product->getEntityId(),
            'product_price' => $this->block->getProductPriceHtml(
                $product,
                'wishlist_configured_price',
                \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ['item' => $wishlistItem]
            ),
            'product_is_saleable_and_visible' => $product->isSaleable() && $product->isVisibleInSiteVisibility(),
            'product_has_required_options' => $product->getTypeInstance()->hasRequiredOptions($product),
            'add_to_cart_params' => $this->wishlistHelper->getAddToCartParams($wishlistItem, true),
            'delete_item_params' => $this->wishlistHelper->getRemoveParams($wishlistItem, true),
        ];
    }
}