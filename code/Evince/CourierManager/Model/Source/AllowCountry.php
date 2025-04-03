<?php

namespace Evince\CourierManager\Model\Source;

use Magento\Directory\Helper\Data as DirectoryHelper;


class AllowCountry {

    protected $scopeConfig;
    
    protected $directoryHelper;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        DirectoryHelper $directoryHelper
        
    ) {
        
        $this->scopeConfig = $scopeConfig;
        $this->directoryHelper = $directoryHelper;
        
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