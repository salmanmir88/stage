<?php

namespace Evince\CustomerResetPassword\Controller\Reset;

class Create extends \Magento\Framework\App\Action\Action {

    protected $resultRedirectFactory;
    protected $url;
    protected $_storeManager;
    protected $_customer;
    protected $_customerRegistry;
    protected $_customerRepositoryInterface;
    protected $_encryptor;
    protected $_messageManager;
    protected $_resourceConnection;

    public function __construct(
        \Magento\Framework\App\Action\Context $context, 
        \Magento\Framework\Controller\Result\Redirect $resultRedirectFactory, 
        \Magento\Framework\UrlInterface $url, 
        \Magento\Store\Model\StoreManagerInterface $storeManager, 
        \Magento\Customer\Model\Customer $customers, 
        \Magento\Customer\Model\CustomerRegistry $customerRegistry, 
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository, 
        \Magento\Framework\Encryption\EncryptorInterface $encryptor, 
        \Magento\Framework\Message\ManagerInterface $messageManager, 
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {

        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->url = $url;
        $this->_storeManager = $storeManager;
        $this->_customer = $customers;
        $this->_customerRegistry = $customerRegistry;
        $this->_customerRepositoryInterface = $customerRepository;
        $this->_encryptor = $encryptor;
        $this->_messageManager = $messageManager;
        $this->_resourceConnection = $resourceConnection;
        parent::__construct($context);
    }

    public function execute() {


        $resultRedirect = $this->resultRedirectFactory->create();

        $post = $this->getRequest()->getPostValue();

        $website_id = $this->_storeManager->getStore()->getWebsiteId();
        $customerModel = $this->_customer;
        $customerModel->setWebsiteId($website_id);
        $customerModel->loadByEmail($post['email']);

        if ($customerModel->getMigrateCustomer() == 1) {
            if ($customerModel->getId()) {
                $customer = $this->_customerRepositoryInterface->getById($customerModel->getId());
                $customerSecure = $this->_customerRegistry->retrieveSecureData($customer->getId());
                $customerSecure->setRpToken(null);
                $customerSecure->setRpTokenCreatedAt(null);
                $customerSecure->setPasswordHash($this->_encryptor->getHash($post['password'], true));
                $this->_customerRepositoryInterface->save($customer);

                $connection = $this->_resourceConnection->getConnection();
                // $table is table name
                $table = $connection->getTableName('customer_entity');
                $customerId = $customerModel->getId();
                $query = "UPDATE `" . $table . "` SET `migrate_customer`= '0' WHERE entity_id = $customerId ";
                $connection->query($query);

                $this->_messageManager->addSuccessMessage(__("Your password updated successfully"));
                $redirectLink = $this->url->getUrl('customer/account/login');
                $redirectUrl = $resultRedirect->setUrl($redirectLink);
                
            } else {
                $this->_messageManager->addNoticeMessage(__("You don't have account with us."));
                $redirectLink = $this->url->getUrl('customer/account/create');
                $redirectUrl = $resultRedirect->setUrl($redirectLink);
            }
        }
        else
        {
            $this->_messageManager->getMessages(true);
            $redirectLink = $this->url->getUrl('customer/account/login');
            $redirectUrl = $resultRedirect->setUrl($redirectLink);
        }
        return $redirectUrl;
    }

}
