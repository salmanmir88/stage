<?php 
namespace Evincemage\FlatRateFree\Helper;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
	public function __construct
	(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Checkout\Model\Cart $cartHelper
	)
	{
		$this->cartHelper = $cartHelper;
		parent::__construct($context);
	}

	public function getConfigValues($config_path)
	{
		return $this->scopeConfig->getValue(
			$config_path,
			ScopeInterface::SCOPE_STORE
		);
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
}
