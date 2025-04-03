<?php
namespace Eextensions\CustomOrderTab\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;

class Save extends \Magento\Backend\App\Action
{
	/**
	* @var \Magento\Backend\Helper\Js
	*/
    protected $_jsHelper;
	/**
	* @var \Magento\Framework\Image\AdapterFactory
	*/
	protected $adapterFactory;

	/**
	* @var \Magento\MediaStorage\Model\File\UploaderFactory
	*/
	protected $uploaderFactory;

	/**
	* @var \Magento\Framework\Filesystem
	*/
	protected $filesystem;
	
	/**
     * @param Action\Context $context
     */
    public function __construct(
		Action\Context $context,
		\Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Helper\Js $jsHelper
	)
    {
		$this->_adapterFactory = $adapterFactory;
		$this->_uploaderFactory = $uploaderFactory;
		$this->_filesystem = $filesystem;
        $this->_jsHelper = $jsHelper;
        parent::__construct($context);
    }

    /**
     * Hello test controller page.
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        // echo __('Hello Save data in controller .');
		
		// $data = $this->getRequest()->getPostValue();
		// pr($data);die;
		
		/** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data = $this->getRequest()->getPostValue()) {
			
			// pr($data);die;
			
			$model = $this->_objectManager->create('Eextensions\CustomOrderTab\Model\Comment');
			$model->addData($data);
			
			// pr($model->getData());die;
			
			try {
				$model->save();
				$this->messageManager->addSuccess(__('The Comment has been submitted successfully.'));
				$this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);
				if ($this->getRequest()->getParam('back')) {
					return $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
				}
				return $resultRedirect>setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
			} catch (\Magento\Framework\Exception\LocalizedException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\RuntimeException $e) {
				$this->messageManager->addError($e->getMessage());
			} catch (\Exception $e) {
				$this->messageManager->addException($e, __('Something went wrong while saving the comment.'));
			}

			$this->_getSession()->setFormData($data);
			$resultRedirect->setPath('*/*/');
		}
		return $resultRedirect->setPath('*/*/');
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eextensions_CustomOrderTab::comment');
    }
}