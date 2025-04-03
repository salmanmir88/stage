<?php
namespace Evince\CreateAcountRedirect\Observer;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Response\Http;
use Magento\Framework\App\ResponseFactory;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\BlockFactory;
use Evince\CreateAcountRedirect\Helper\Data;


/**
* This is the extension to redirect into customer account edit page
*
*/

class Register implements ObserverInterface {

    protected $_responseFactory;
    protected $_redirect;
    protected $_url;
    protected $session;
    protected $_helper;
    
    public function __construct(
        BlockFactory $blockFactory,
        ResponseFactory $responseFactory,
        UrlInterface $url,
        Http $redirect,
        Session $customerSession,
        Data $helper
    ) {
        $this->_responseFactory = $responseFactory;
        $this->_url = $url;
        $this->_redirect = $redirect;
        $this->session = $customerSession;
        $this->_helper = $helper;
    }

    /**
     * After customer registration it will redirect to the specified custom url
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        //$status = $this->_helper->getisEnabled();
        //if (!empty($status)) {
            $thank_you_page = $this->_helper->getCustomUrl();
            $customer = $observer->getCustomer();
            $this->session->setCustomerDataAsLoggedIn($customer);
            $customRedirectionUrl = $this->_url->getUrl($thank_you_page);
            $this->_responseFactory->create()->setRedirect($customRedirectionUrl)->sendResponse();   
            die();
        //}           
    }
}