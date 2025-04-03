<?php 
namespace Evincemage\FlatRateFree\Plugin\Model\Carrier;
use Magento\Quote\Model\Quote\Address\RateRequest;

class FlatratePlugin extends \Magento\OfflineShipping\Model\Carrier\Flatrate
{
	public function __construct
	(
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
		\Psr\Log\LoggerInterface $logger,
		\Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\OfflineShipping\Model\Carrier\Flatrate\ItemPriceCalculator $itemPriceCalculator,
		\Evincemage\FlatRateFree\Helper\Data $thresholdHelper,
		array $data = []
	)
	{	
		$this->thresholdHelper = $thresholdHelper;
		parent::__construct($scopeConfig, $rateErrorFactory, $logger, $rateResultFactory, $rateMethodFactory, $itemPriceCalculator, $data);
	}

	public function aroundCollectRates(\Magento\OfflineShipping\Model\Carrier\Flatrate $subject, callable $proceed, RateRequest $request)
	{
		
		$cartSubtotal = (float)$this->thresholdHelper->getCurrentCartSubTotal();
		$minimumOrderAmount = (float) $this->thresholdHelper->getConfigValues('flatshipping/general/threshold');

		if($cartSubtotal!=false)
		{
			if($minimumOrderAmount>0)
			{

				if($cartSubtotal>=$minimumOrderAmount)
				{
					$newresult = $this->_rateResultFactory->create();
					$shippingPrice = 0.0;
					$method = $this->createResultMethod($shippingPrice);
					$newresult->append($method);
					return $newresult;
				}		
			}
		}
			
		
		$result = $proceed($request);
		return $result;
	}

	public function getCurrentCartSubTotal()
	{
		$cartId = $this->cartHelper->getQuote()->getId();
		if($cartId)
		{
			return $this->cartHelper->getQuote()->getSubtotal(); 
		}

		return false;
	}

	private function createResultMethod($shippingPrice)
    {
        /** @var \Magento\Quote\Model\Quote\Address\RateResult\Method $method */
        $method = $this->_rateMethodFactory->create();

        $method->setCarrier('flatrate');
        $method->setCarrierTitle($this->getConfigData('title'));

        $method->setMethod('flatrate');
        $method->setMethodTitle($this->getConfigData('name'));

        $method->setPrice($shippingPrice);
        $method->setCost($shippingPrice);
        return $method;
    }
}