<?php

namespace Evince\CreateAcountRedirect\Observer;

use Magento\Framework\Event\ObserverInterface;

class CheckLoginPersistentObserver implements ObserverInterface {

    protected $redirect;
    protected $customerSession;
    protected $messageManager;
    

    public function __construct(
        \Magento\Customer\Model\Session $customerSession, 
        \Magento\Framework\App\Response\RedirectInterface $redirect,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->customerSession = $customerSession;
        $this->redirect = $redirect;
        $this->messageManager = $messageManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer) {
        $actionName = $observer->getEvent()->getRequest()->getFullActionName();
        $controller = $observer->getControllerAction();
        if ($this->customerSession->isLoggedIn()) {
            if($this->customerSession->getCustomer()->getData('firstname') == "General" && 
                    $this->customerSession->getCustomer()->getData('lastname') == "Customer"){
                
                if ($actionName != 'customer_account_edit') {
                    
                    $this->messageManager->addWarningMessage(__('Please fill your First Name and Last Name'));
                    $this->redirect->redirect($controller->getResponse(), 'customer/account/edit/');
                    
                }
                
            }
        }
    }
}
