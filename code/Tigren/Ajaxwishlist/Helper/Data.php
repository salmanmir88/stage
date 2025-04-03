<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Json\DecoderInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;
use Tigren\Ajaxsuite\Helper\Data as AjaxsuiteHelper;

/**
 * Class Data
 * @package Tigren\Ajaxwishlist\Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var
     */
    protected $_storeId;

    /**
     * @var Registry
     */
    protected $_coreRegistry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    /**
     * @var LayoutFactory
     */
    protected $_layoutFactory;

    /**
     * @var EncoderInterface
     */
    protected $_jsonEncoder;

    /**
     * @var DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var AjaxsuiteHelper
     */
    protected $_ajaxSuite;

    /**
     * @var \Magento\Wishlist\Block\Customer\Wishlist
     */
    protected $_wishList;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $_wishListHelper;

    /**
     * @var
     */
    protected $_productIds;

    /**
     * Data constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param Registry $coreRegistry
     * @param CustomerSession $customerSession
     * @param LayoutFactory $layoutFactory
     * @param EncoderInterface $jsonEncoder
     * @param DecoderInterface $jsonDecoder
     * @param AjaxsuiteHelper $ajaxSuite
     * @param \Magento\Wishlist\Block\Customer\Wishlist $wishList
     * @param \Magento\Wishlist\Helper\Data $wishListHelper
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        EncoderInterface $jsonEncoder,
        DecoderInterface $jsonDecoder,
        AjaxsuiteHelper $ajaxSuite,
        \Magento\Wishlist\Block\Customer\Wishlist $wishList,
        \Magento\Wishlist\Helper\Data $wishListHelper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->_layoutFactory = $layoutFactory;
        $this->_jsonEncoder = $jsonEncoder;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_ajaxSuite = $ajaxSuite;
        $this->_wishList = $wishList;
        $this->_wishListHelper = $wishListHelper;
    }

    /**
     * @return string
     */
    public function getAjaxWishlistInitOptions()
    {
        $optionsAjaxsuite = $this->_jsonDecoder->decode($this->_ajaxSuite->getAjaxSuiteInitOptions());
        $customerId = $this->_customerSession->getCustomer()->getId();
        $isEnable = $this->isEnabledAjaxWishlist();
        if($this->isWishlistPlusEnabled()){
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $wishlistPlusHelper = $objectManager->get('Tigren\WishlistPlus\Helper\Data');
            if($wishlistPlusHelper->isWishlistPlusEnable()){
                $isEnable = 0;
            }
        }
        $options = [
            'ajaxWishlist' => [
                'enabled' => $isEnable,
                'ajaxWishlistUrl' => $this->_getUrl('ajaxwishlist/wishlist/showPopup'),
                'loginUrl' => $this->_getUrl('customer/account/login'),
                'customerId' => $customerId,
            ],
        ];
        return $this->_jsonEncoder->encode(array_merge($optionsAjaxsuite, $options));
    }


    /**
     * @return bool
     */
    public function isEnabledAjaxWishlist()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxwishlist/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getOptionsPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $update = $layout->getUpdate();
        $update->load('ajaxwishlist_options_popup');
        $layout->generateXml();
        $layout->generateElements();
        return $layout->getOutput();
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSuccessHtml()
    {
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle('ajaxwishlist_success_message')->load();
        $layout->generateXml();
        $layout->generateElements();
        $result = $layout->getOutput();
        $layout->__destruct();
        return $result;
    }

    /**
     * @param $product
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getErrorHtml($product)
    {
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle('ajaxwishlist_error_message')->load();
        $layout->generateXml();
        $layout->generateElements();
        $result = $layout->getOutput();
        $layout->__destruct();
        return $result;
    }

    /**
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getSuccessRemoveHtml()
    {
        $layout = $this->_layoutFactory->create(['cacheable' => false]);
        $layout->getUpdate()->addHandle('ajaxwishlist_remove_success_message')->load();
        $layout->generateXml();
        $layout->generateElements();
        $result = $layout->getOutput();
        $layout->__destruct();
        return $result;
    }

    public function isWishlistPlusEnabled()
    {
        return $this->_moduleManager->isEnabled('Tigren_WishlistPlus');
    }
}
