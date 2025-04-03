<?php
/**
 * Copyright Â© Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Developerswing\Flatrate\Model\OfflineShipping\Carrier\Override;

use Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\Carrier\CarrierInterface;
use Magento\Shipping\Model\Rate\Result;
use Magento\Framework\Pricing\Helper\Data;
/**
 * Flat rate shipping model
 *
 * @api
 * @since 100.0.2
 */
class FlatrateOverride extends AbstractCarrier implements CarrierInterface
{
    /**
     * @var string
     */
    protected $_code = 'flatrate';

    /**
     * @var bool
     */
    protected $_isFixed = true;

    /**
     * @var \Magento\Shipping\Model\Rate\ResultFactory
     */
    protected $_rateResultFactory;

    /**
     * @var \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory
     */
    protected $_rateMethodFactory;

    /**
     * @var ItemPriceCalculator
     */
    private $itemPriceCalculator;

    protected $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param ItemPriceCalculator $itemPriceCalculator
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator $itemPriceCalculator,
        Data $priceHelper,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->itemPriceCalculator = $itemPriceCalculator;
        $this->scopeConfig = $scopeConfig;
        $this->priceHelper = $priceHelper;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * @param RateRequest $request
     * @return Result|bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function collectRates(RateRequest $request)
    {
        if (!$this->getConfigFlag('active')) {
            return false;
        }

        $freeBoxes = $this->getFreeBoxesCount($request);
        $this->setFreeBoxes($freeBoxes);

        /** @var Result $result */
        $result = $this->_rateResultFactory->create();

        $shippingPrice = $this->getShippingPrice($request, $freeBoxes);

        if ($shippingPrice !== false) {
            $method = $this->createResultMethod($shippingPrice,$request);
            $result->append($method);
        }

        return $result;
    }

    /**
     * @param RateRequest $request
     * @return int
     */
    private function getFreeBoxesCount(RateRequest $request)
    {
        $freeBoxes = 0;
        if ($request->getAllItems()) {
            foreach ($request->getAllItems() as $item) {
                if ($item->getProduct()->isVirtual() || $item->getParentItem()) {
                    continue;
                }

                if ($item->getHasChildren() && $item->isShipSeparately()) {
                    $freeBoxes += $this->getFreeBoxesCountFromChildren($item);
                } elseif ($item->getFreeShipping()) {
                    $freeBoxes += $item->getQty();
                }
            }
        }
        return $freeBoxes;
    }

    /**
     * @return array
     */
    public function getAllowedMethods()
    {
        return ['flatrate' => $this->getConfigData('name')];
    }

    /**
     * @param RateRequest $request
     * @param int $freeBoxes
     * @return bool|float
     */
    private function getShippingPrice(RateRequest $request, $freeBoxes)
    {
        
        $shippingPrice = false;

        $configPrice = $this->getConfigData('price');
        if ($this->getConfigData('type') === 'O') {
            // per order
            $shippingPrice = $this->itemPriceCalculator->getShippingPricePerOrder($request, $configPrice, $freeBoxes);
        } elseif ($this->getConfigData('type') === 'I') {
            // per item
            $shippingPrice = $this->itemPriceCalculator->getShippingPricePerItem($request, $configPrice, $freeBoxes);
        }

        $shippingPrice = $this->getFinalPriceWithHandlingFee($shippingPrice);

        if ($shippingPrice !== false && $request->getPackageQty() == $freeBoxes) {
            $shippingPrice = '0.00';
        }      

        return $shippingPrice;
    }

    /**
     * @param int|float $shippingPrice
     * @return \Magento\Quote\Model\Quote\Address\RateResult\Method
     */
    private function createResultMethod($shippingPrice,$request)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();
        $moduleStatus  = $this->moduleConfig('flatratepriceandmessage/configuration/enable');
        $name = $this->getConfigData('name');
        if($moduleStatus){        
                if($request->getDestCountryId()=='BH')
                {
                 $name         = $this->moduleConfig('flatratepriceandmessage/configuration/bahrain_message');
                 $shippingPrice = $this->moduleConfig('flatratepriceandmessage/configuration/bahrain_price');
                }elseif ($request->getDestCountryId()=='KW') {  
                 $name         = $this->moduleConfig('flatratepriceandmessage/configuration/kuwait_message');
                 $shippingPrice = $this->moduleConfig('flatratepriceandmessage/configuration/kuwait_price');        
                }elseif ($request->getDestCountryId()=='OM') {
                 $name         = $this->moduleConfig('flatratepriceandmessage/configuration/oman_message');
                 $shippingPrice = $this->moduleConfig('flatratepriceandmessage/configuration/oman_price');      
                }elseif ($request->getDestCountryId()=='SA') {
                 $name         = $this->moduleConfig('flatratepriceandmessage/configuration/saudi_arabia_message');
                 $shippingPrice = $this->moduleConfig('flatratepriceandmessage/configuration/saudi_arabia_price');  
                }elseif ($request->getDestCountryId()=='AE') {
                 $name         = $this->moduleConfig('flatratepriceandmessage/configuration/united_arab_emirates_message');
                 $shippingPrice = $this->moduleConfig('flatratepriceandmessage/configuration/united_arab_emirates_price');      
                }
        }
        
        $shippingPriceCondition = $this->moduleConfig('flatratepriceandmessage/configuration/condition_for_shipping');
        $shippingPriceCondition = $this->priceHelper->currency($shippingPriceCondition, false, false);
        $subtotalPrice          = $this->priceHelper->currency($request->getBaseSubtotalInclTax(), false, false);
        
        if($subtotalPrice>=$shippingPriceCondition)
        {
          $shippingPrice = 0;
        }
        $method->setCarrier('flatrate');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('flatrate');
        $method->setMethodTitle($name);

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }

    /**
     * @param mixed $item
     * @return mixed
     */
    private function getFreeBoxesCountFromChildren($item)
    {
        $freeBoxes = 0;
        foreach ($item->getChildren() as $child) {
            if ($child->getFreeShipping() && !$child->getProduct()->isVirtual()) {
                $freeBoxes += $item->getQty() * $child->getQty();
            }
        }
        return $freeBoxes;
    }
    public function moduleConfig($path)
    {
        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getStore()
        );
    }
}