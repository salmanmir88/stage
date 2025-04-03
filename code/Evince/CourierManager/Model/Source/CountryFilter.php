<?php
namespace Evince\CourierManager\Model\Source;
use Magento\Framework\Option\ArrayInterface;
use Magento\Directory\Helper\Data as DirectoryHelper;

class CountryFilter implements ArrayInterface
{
	protected $scopeConfig;
    
    protected $directoryHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DirectoryHelper $directoryHelper
        
    ) {
        
        $this->scopeConfig = $scopeConfig;
        $this->directoryHelper = $directoryHelper;
        
    }
	public function toOptionArray()
	{
		$result = [];
		$result = $this->getAllowedCountries();	
		return $result;

	}

	public function getAllowedCountries()
    {
        $countries = [];

        /* @var Country $country */
        foreach ($this->directoryHelper->getCountryCollection() as $country) {
            $countries[] = [
                'value' => $country->getId(),
                'label' => $country->getName()
            ];
        }
        return $countries;
    }
}