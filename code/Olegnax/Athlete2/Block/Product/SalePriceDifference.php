<?php
namespace Olegnax\Athlete2\Block\Product;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Block\Product\Context;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Olegnax\Athlete2\Helper\Helper as ThemeHelper;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableType;
use Magento\Framework\Escaper;

class SalePriceDifference extends AbstractProduct
{
    /**
     * @var ThemeHelper
     */
    private $helper;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var PriceHelper
     */
    /**
     * @var Escaper
     */
    private $escaper;
    protected $_priceHelper;
    protected $priceText;
    /**
     * SalePriceDifference constructor.
     */
    public function __construct(
        Context $context,
        PriceHelper $priceHelper,
        ThemeHelper $helper,
        Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_priceHelper = $priceHelper;
        $this->helper = $helper;
        $this->logger = $context->getLogger();
        $this->escaper = $escaper;
    }
    /**
     * @return string
     */
    public function getSalePriceDifference(Product $product)
    {
        if (!$this->isEnabled() || !$product) {
            return '';
        }

        $showAsPercent = $this->getConfigShowPriceAsPercent();

       // Handle configurable products
        if ($product->getTypeId() === ConfigurableType::TYPE_CODE) {
            // add empty tag for js
            return $this->wrapPriceDifference($this->getPriceDiffTag(''), true, $showAsPercent);
        }
        
        // Handle simple products
        $prices = $this->getSalePriceSimple($product, $showAsPercent);
        return $prices ? $this->wrapPriceDifference($prices, false, $showAsPercent) : '';
    }
    
    private function wrapPriceDifference(string $prices, bool $isConfigurable = false, bool $showAsPercent = false): string
    {
        $classes = 'ox-sale-price-dif ';
        $classes .= $isConfigurable ? ' config d-none' : '';
        $classes .= $showAsPercent ? ' as-percent' : '';
        return '<div class="' . $this->escaper->escapeHtmlAttr($classes) .'">'. $prices . '</div>';
    }

    public function getSalePriceSimple(Product $product, $showAsPercent){
        $finalPrice = $product->getFinalPrice();
        $oldPrice = $product->getPrice();

        if($showAsPercent){
            $priceDifference = round((1 - $finalPrice / $oldPrice) * 100);
        } else{
            $priceDifference = $oldPrice - $finalPrice;
        }

        // if is on sale (has special price)
        if($priceDifference > 0){
            return $this->getPriceDiffTag($priceDifference, !$showAsPercent);
        }
        return '';
    }

    public function getPriceDiffTag($priceDifference, $formatPrice = true){
        //get custom text for label or use defaul if empty
        if(!$this->priceText){
            $this->priceText = $this->getConfig('athlete2_settings/products_listing/show_price_diff_text');
        }
        $text = $this->priceText;
        
        if(!empty($text)){
            $text = str_replace('{{price_diff}}', $this->getPriceTag($priceDifference, $formatPrice), (string)$text);
        } else {
            $text = __('Save %1', $this->getPriceTag($priceDifference, $formatPrice));
        }
        return '<span class="price-dif__container">' . (string)$text . '</span>';        
    }

    public function getPriceTag($priceDifference, $format = true){
        if($priceDifference){
            $price = ($format ? $this->formatPrice($priceDifference) : $priceDifference);
        } else{
            $price = '';
        }
        return '<span class="js-ox-price-diff ox-price-diff-value">'. $price . '</span>';
    }
    /**
     * Format the given price using the currency helper.
     *
     * @param float $price The price to be formatted.
     * @return string Formatted price string.
     */
    public function formatPrice($price)
    {
        // Use the _priceHelper object to format the price as currency.
        // The currency method takes three parameters:
        //   1. The price to be formatted.
        //   2. True to include the currency symbol.
        //   3. False to include the thousand separator.
        return $this->_priceHelper->currency($price, true, false);
    }

    public function getConfig($path, $storeCode = null){
        return $this->helper->getConfig($path, $storeCode);
    }

    public function getConfigShowPriceAsPercent(){
        return (bool)$this->helper->getConfig('athlete2_settings/products_listing/price_diff_percent');
    }
    public function isEnabled(){
        return $this->helper->isEnabled() &&
         (bool)$this->helper->getConfig('athlete2_settings/products_listing/show_price_diff');
    }
}