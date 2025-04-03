<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block;

use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\Template\Context;
use Tigren\Ajaxwishlist\Helper\Data;

/**
 * Class Js
 * @package Tigren\Ajaxwishlist\Block
 */
class Js extends \Magento\Framework\View\Element\Template
{
    /**
     * @var string
     */
    protected $_template = 'js/main.phtml';

    /**
     * @var Data
     */
    protected $_ajaxwishlistHelper;

    /**
     * Js constructor.
     * @param Context $context
     * @param Data $ajaxwishlistHelper
     * @param array $data
     */

    protected $_wishList;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishListHelper;

    /**
     * @var
     */
    protected $_products;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * Js constructor.
     * @param Context $context
     * @param Data $ajaxwishlistHelper
     * @param \Magento\Wishlist\Block\Customer\Wishlist $wishList
     * @param \Magento\Wishlist\Helper\Data $wishListHelper
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $ajaxwishlistHelper,
        \Magento\Wishlist\Block\Customer\Wishlist $wishList,
        \Magento\Wishlist\Helper\Data $wishListHelper,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        parent::__construct($context, $data);

        $this->_wishList = $wishList;
        $this->_wishListHelper = $wishListHelper;
        $this->_ajaxwishlistHelper = $ajaxwishlistHelper;
        $this->_jsonEncoder = $jsonEncoder;
    }

    /**
     * @return string
     */
    public function getAjaxWishlistInitOptions()
    {
        return $this->_ajaxwishlistHelper->getAjaxWishlistInitOptions();
    }

    /**
     * @return int|void
     */
    public function getProductsNumber()
    {
        $products = $this->_products;
        if ($products === null) {
            $connection = $this->_wishList->getWishlistItems()->getConnection();
            $select = $this->_wishList->getWishlistItems()->getSelect();
            $items = $connection->fetchAll($select);
            $products = count($items);
            return $products;
        }
        return $this->_products;
    }

}