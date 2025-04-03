<?php 
namespace Evince\ViewAll\Helper;

class CurrentCategory extends \Magento\Framework\App\Helper\AbstractHelper
{
	public function __construct
	(
		\Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver
	)
	{
		$this->layerResolver = $layerResolver;
		parent::__construct($context);	
	}

	public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }
}