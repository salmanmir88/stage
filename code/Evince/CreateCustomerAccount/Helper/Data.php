<?php

namespace Evince\CreateCustomerAccount\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {
    
    protected $courierManagerFactory;

    public function __construct(
        \Evince\CourierManager\Model\GridFactory $courierManagerFactory
    ) {
        $this->courierManagerFactory = $courierManagerFactory;
    }
    
    public function getCityList() {
        
        $cityList = [];
        
        $resultPage = $this->courierManagerFactory->create();
        $collection = $resultPage->getCollection(); 
        
        foreach ($collection as $city) {
            $cityList[] = [
                'value' => $city['city'],
                'label' => $city['city']
            ];
        }
        return $cityList;

    }

}
