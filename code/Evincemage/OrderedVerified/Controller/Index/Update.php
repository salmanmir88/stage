<?php
namespace Evincemage\OrderedVerified\Controller\Index;
use Magento\Framework\App\ResourceConnection;

class Update extends \Magento\Framework\App\Action\Action
{
	protected $resultPageFactory;

	public function __construct
	(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\View\Result\PageFactory $resultPageFactory,
		\Magento\Framework\Controller\Result\RedirectFactory $resultRedirectFactory,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		ResourceConnection $resourceConnection
	)
	{
		$this->resultPageFactory = $resultPageFactory;
		$this->resultRedirectFactory = $resultRedirectFactory;
		$this->resourceConnection = $resourceConnection;
		$this->_messageManager = $messageManager;
        parent::__construct($context);
	}

	public function execute()
	{
		try
		{
			$postData = $this->getRequest()->getPost();
			$orderIncId = '0';
			$is_verified = '0';
			$finalMobileNo = '0';

			if(isset($postData['order_in_id']))
			{
				$orderIncId = $postData['order_in_id'];
						
			}
			if(isset($postData['is_verified']))
			{
				$is_verified = $postData['is_verified'];
			}

			if(isset($postData['final_mobile_number']))
			{
				$finalMobileNo = $postData['final_mobile_number'];
			}
			if($orderIncId!='0')
			{
				$connection = $this->resourceConnection->getConnection();
				$table = $connection->getTableName('sales_order_grid');	
				$query = "UPDATE `" . $table . "` SET `is_order_verified`=".$is_verified."  WHERE increment_id = $orderIncId";
        		$connection->query($query);
        		if($finalMobileNo!='0')
        		{
        			$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
            		$orderData = $objectManager->create('Magento\Sales\Model\Order')->loadByIncrementId($orderIncId); 
            		$orderData->getShippingAddress()->setTelephone($finalMobileNo);
            		$orderData->getBillingAddress()->setTelephone($finalMobileNo);
            		$orderData->save(); 
        		}
        		      		
				$resultRedirect = $this->resultRedirectFactory->create();
				$resultRedirect->setPath('home');
				if($is_verified=='1')
				{
					$this->_messageManager->addNotice(__("Order Code has been verified. Order was Saved"));
				}
				else
				{
					$this->_messageManager->addNotice(__("Order Code coluld not be verified. Order was Saved"));	
				}
				$session = $this->getOnepage()->getCheckout();
				$session->clearQuote();
				return $resultRedirect;		
			} 	
			
				
		}
		catch (\Exception $e)
		{
			$resultRedirect = $this->resultRedirectFactory->create();
			$resultRedirect->setPath('home');
			$this->_messageManager->addError(__("Something went wrong while updating Order Code Status"));
			return $resultRedirect;
				
		}	
		
        
        
	}

	public function getOnepage()
    {
    	$objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager->get(\Magento\Checkout\Model\Type\Onepage::class);
    }
}