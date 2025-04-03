<?php

namespace Eextensions\CustomOrderTab\Block\Adminhtml\Order\View\Tab;

use Magento\Framework\UrlInterface;

class CommentTab extends \Magento\Backend\Block\Template implements \Magento\Backend\Block\Widget\Tab\TabInterface
{
	protected $_template = 'order/view/tab/comment_tab.phtml';
	/**
		* @var \Magento\Framework\Registry
		*/
	private $_coreRegistry;
	
	protected $authSession;
	
	protected $commentCollectionFactory;
	
	/**
	* View constructor.
	* @param \Magento\Backend\Block\Template\Context $context
	* @param \Magento\Framework\Registry $registry
	* @param array $data
	*/
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Backend\Model\Auth\Session $authSession, 
		UrlInterface $urlBuilder,
		\Eextensions\CustomOrderTab\Model\ResourceModel\Comment\CollectionFactory $commentCollectionFactory,
		array $data = []
	) {
		$this->_coreRegistry = $registry;
		$this->authSession = $authSession;
		$this->urlBuilder = $urlBuilder;
		$this->commentCollectionFactory = $commentCollectionFactory;
		parent::__construct($context, $data);
	}
	
	/**
	* Retrieve order model instance
	* 
	* @return \Magento\Sales\Model\Order
	*/
	public function getOrder()
	{
		return $this->_coreRegistry->registry('current_order');
	}
	/**
	* Retrieve order model instance
	*
	* @return int
	*Get current id order
	*/
	public function getOrderId()
	{
		return $this->getOrder()->getEntityId();
	}
	
	/**
	* Retrieve order increment id
	*
	* @return string
	*/
	public function getOrderIncrementId()
	{
		return $this->getOrder()->getIncrementId();
	}
	/**
	* {@inheritdoc}
	*/
	public function getTabLabel()
	{
		return __('Add Order Comment');
	}
	
	/**
	* {@inheritdoc}
	*/
	public function getTabTitle()
	{
		return __('Add Order Comment');
	}
	
	/**
	* {@inheritdoc}
	*/
	public function canShowTab()
	{
		return true;
	}
	
	/**
	* {@inheritdoc}
	*/
	public function isHidden()
	{
		return false;
	}
   
	public function getPostUrl()
    {
        return $this->urlBuilder->getUrl('customordertab/comment/save');
    }
   
   /**
    * Retrieve backend model instance
    *
    * @return \Magento\Backend\Model\Auth\Session
    */
	public function getAdminCurrentUser()
	{
		return $this->authSession->getUser();
	}
	
	/**
    * Retrieve admin user id
    *
    * @return string
    */
	public function getAdminUserId()
	{
		return $this->authSession->getUser()->getUserId();
	}
	
	/**
    * Retrieve admin user name
    *
    * @return string
    */
	public function getAdminUserName()
	{
		return $this->authSession->getUser()->getUsername();
	}
	
	/**
    * Retrieve admin user email
    *
    * @return string
    */
	public function getAdminUserEmail()
	{
		return $this->authSession->getUser()->getEmail();
	}
	
	public function getCommentCollection()
	{
		$orderId = $this->getOrderId();
		
		$collection  = $this->commentCollectionFactory->create();
		$collection->addFieldToFilter('order_id', $orderId);
		$collection->setOrder('id','DESC');
		
		return $collection;
	}
}