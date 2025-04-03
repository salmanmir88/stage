<?php
namespace Evince\CreateAcountRedirect\Observer;

use Magento\Framework\Event\ObserverInterface;

class AfterEditAccount implements ObserverInterface {

    
    protected $messageManager;
    protected $responseFactory;
    protected $url;


    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url
    ) {
        
        $this->messageManager = $messageManager;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
    }

    
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($_POST['firstname'] == "General" && $_POST['lastname'] == "Customer")
        {
            $redirectionUrl = $this->url->getUrl('customer/account/edit/');
            $this->messageManager->addWarningMessage(__('Please fill your First Name and Last Name'));
            $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
            die();
            
        }
        else
        {
            $this->messageManager->getMessages(true);
            $redirectionUrl = $this->url->getUrl('customer/account/');
            $this->responseFactory->create()->setRedirect($redirectionUrl)->sendResponse();
            
        }
        return $this;

    }
}