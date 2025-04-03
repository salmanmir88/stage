<?php

namespace Evince\CourierManager\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;

class AvailShipMethods {

    protected $scopeConfig;
    protected $shippingmodelconfig;

    public function __construct(Config $shippingmodelconfig, ScopeConfigInterface $scopeConfig) {
        $this->shippingmodelconfig = $shippingmodelconfig;
        $this->scopeConfig = $scopeConfig;
    }

//    public function getActiveShippingMethod() {
//        $shippings = $this->shippingmodelconfig->getActiveCarriers();
//        $methods = array();
//        foreach ($shippings as $shippingCode => $shippingModel) {
//            if ($carrierMethods = $shippingModel->getAllowedMethods()) {
//                foreach ($carrierMethods as $methodCode => $method) {
//                    $code = $shippingCode . '_' . $methodCode;
//                    $carrierTitle = $this->scopeConfig->getValue('carriers/' . $shippingCode . '/title');
//                    $methods[] = array('value' => $code, 'label' => $carrierTitle);
//                }
//            }
//        }
//        return $methods;
//    }
    
    public function toOptionArray()
    {
        return [
            ['value' => 'aramex', 'label' => __('Aramex')],
            ['value' => 'fetchr', 'label' => __('Fetchr')],
            ['value' => 'saee', 'label' => __('Saee')]
        ];
    }

}
