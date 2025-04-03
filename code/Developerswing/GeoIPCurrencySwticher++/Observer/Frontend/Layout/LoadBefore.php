<?php
/**
 * Copyright © Geo IP Currency Swticher All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Developerswing\GeoIPCurrencySwticher\Observer\Frontend\Layout;

class LoadBefore implements \Magento\Framework\Event\ObserverInterface
{

    protected $logger;
    protected $helper;
    protected $_storeManager;
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Developerswing\GeoIPCurrencySwticher\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->logger        = $logger;
        $this->helper        = $helper;
        $this->_storeManager = $storeManager;
    }
    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        if(!$this->helper->getModuleStatus())
        {
            return;
        } 
        
        $currency    = 'SAR';
        $countryCode =  $this->helper->getCountryCode();
        if($countryCode=='BH')
        {
            $currency = 'BHD';
        }elseif($countryCode=='KW'){
            $currency = 'KWD'; 
        }elseif($countryCode=='OM'){
            $currency = 'OMR'; 
        }elseif($countryCode=='SA'){
            $currency = 'SAR'; 
        }elseif($countryCode=='AE'){
            $currency = 'AED';
        }else{
            $currency = 'USD';
        }
        if ($currency) {
           $this->_storeManager->getStore()->setCurrentCurrencyCode($currency);
        }

    }
}

