<?php

namespace Evince\Websettings\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $timezone;
    protected $session;
    protected $storeManager;
    
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Customer\Model\Session $session,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->timezone = $timezone;
        $this->session = $session;
        $this->storeManager = $storeManager;
    }
    
    public function changeDateFormat($releaseDate)
    {
        $dateFormat = $this->timezone->date(new \DateTime($releaseDate))->format('d-m-Y');
        return $dateFormat;
    }
    
    public function IsCustomerLogin()
    {
        if ($this->session->isLoggedIn()) {
            return true;
        } else {
            return false;
        }
    }
    
    public function getCustomerName() {
        if ($this->session->isLoggedIn()) {
            return $this->session->getCustomer()->getName();
        }
    }
    
    public function getBaseUrl()
    {
        return $this->storeManager->getStore()->getBaseUrl();
    }
    
    public function getCurrentStoreView()
    {
        return $this->storeManager->getStore()->getCode();
    }

}
