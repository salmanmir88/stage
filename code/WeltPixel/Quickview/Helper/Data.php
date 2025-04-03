<?php

namespace WeltPixel\Quickview\Helper;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
	 const XML_PATH_QUICKVIEW_ENABLED = 'weltpixel_quickview/general/enable_product_listing';
    const XML_PATH_QUICKVIEW_BUTTONSTYLE = 'weltpixel_quickview/general/button_style';
    
     /**
     * @var \Magento\Framework\UrlInterface 
     */
    protected $urlInterface;
    
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface 
     */
    
    
    
    /**
     * @var array
     */
    protected $_quickviewOptions;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
        
    ) {
		$this->urlInterface = $urlInterface;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
        $this->_quickviewOptions = $this->scopeConfig->getValue('weltpixel_quickview', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return string
     */
    public function getSkuTemplate() {
        $removeSku = $this->_quickviewOptions['general']['remove_sku'];
        if (!$removeSku) {
            return 'Magento_Catalog::product/view/attribute.phtml';
        }

        return '';
    }

    /**
     * @return string
     */
    public function getCustomCSS() {
        return trim($this->_quickviewOptions['general']['custom_css']);
    }

    /**
     * @return int
     */
    public function getCloseSeconds() {
        return trim($this->_quickviewOptions['general']['close_quickview']);
    }

    /**
     * @return boolean
     */
    public function getScrollAndOpenMiniCart() {
        return $this->_quickviewOptions['general']['scroll_to_top'];
    }

    /**
     * @return boolean
     */
    public function getShoppingCheckoutButtons() {
        return $this->_quickviewOptions['general']['enable_shopping_checkout_product_buttons'];
    }
    
    public function getConfigData($path)
	{
		$value = $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		return $value;
	}
	
	
	
	
	public function getQuickViewOnList($product)
    {
		$result = "";
		$isEnabled = $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_ENABLED,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
		$isEnabled = true;
        if ($isEnabled) {
            $buttonStyle =  'weltpixel_quickview_button_' . $this->scopeConfig->getValue(self::XML_PATH_QUICKVIEW_BUTTONSTYLE,  \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $productUrl = $this->urlInterface->getUrl('weltpixel_quickview/catalog_product/view', array('id' => $product->getId()));
            return $result . '<a class="btn btn-success weltpixel-quickview '.$buttonStyle.'" data-quickview-url=' . $productUrl . ' href="javascript:void(0);"><span>' . __("Quick view") . '</span></a>';
        }
	}
	
	
	

}
