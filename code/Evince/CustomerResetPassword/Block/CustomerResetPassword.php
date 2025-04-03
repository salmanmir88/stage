<?php

namespace Evince\CustomerResetPassword\Block;

use Magento\Customer\Model\AccountManagement;

class CustomerResetPassword extends \Magento\Framework\View\Element\Template
{
    protected $_scopeConfig;
    
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_scopeConfig = $scopeConfig;

    }
    
    public function getFormAction()
    {
        return $this->getUrl('customerpassword/reset/create', ['_secure' => true]);
    }
    
    /**
     * Get minimum password length
     *
     * @return string
     * @since 100.1.0
     */
    public function getMinimumPasswordLength()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_MINIMUM_PASSWORD_LENGTH);
    }
    
    /**
     * Get minimum password length
     *
     * @return string
     * @since 100.1.0
     */
    public function getRequiredCharacterClassesNumber()
    {
        return $this->_scopeConfig->getValue(AccountManagement::XML_PATH_REQUIRED_CHARACTER_CLASSES_NUMBER);
    }
    
}