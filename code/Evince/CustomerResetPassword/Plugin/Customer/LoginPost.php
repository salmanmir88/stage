<?php

namespace Evince\CustomerResetPassword\Plugin\Customer;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\UrlInterface;

class LoginPost {

    protected $_resultFactory;
    protected $_url;
    protected $_request;
    protected $_response;
    protected $_storeManager;
    protected $_customer;
    protected $_messageManager;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        UrlInterface $url, 
        ResultFactory $resultFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Customer\Model\Customer $customers,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_request = $context->getRequest();
        $this->_response = $context->getResponse();
        $this->_url = $url;
        $this->_resultFactory = $resultFactory;
        $this->_storeManager = $storeManager;
        $this->_customer = $customers;
        $this->_messageManager = $messageManager;
    }

    public function aroundExecute(\Magento\Customer\Controller\Account\LoginPost $subject, \Closure $proceed) {
        /* Execute code before the original function */
        $login = $this->_request->getPost('login');
        $custom_redirect = false;
        if (isset($login['username'])) {
            
            $website_id = $this->_storeManager->getStore()->getWebsiteId();
            $CustomerModel = $this->_customer;
            $CustomerModel->setWebsiteId($website_id);
            $CustomerModel->loadByEmail($login['username']);
            $isMigrateCustomer = $CustomerModel->getMigrateCustomer();
            if ($isMigrateCustomer == 1) {
                $custom_redirect = true;
            }
        }

        $resultProceed = $proceed(); // Original function
        /* Execute code after the original function */
        if ($custom_redirect) {
            
            $this->_messageManager->getMessages(true);
            $result = $this->_resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $result->setUrl($this->_url->getUrl('customerpassword/reset/index/'));
            return $result;
        }

        return $resultProceed;
    }

}
