<?php

namespace Evince\Websettings\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Websettings extends \Magento\Framework\View\Element\Template {

    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context, 
        ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    public function getPhoneNumber() {

        return $this->scopeConfig->getValue('general/store_information/phone', ScopeInterface::SCOPE_STORE);
    }

}
