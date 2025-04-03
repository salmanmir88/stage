<?php

namespace Eextensions\CustomOrderTab\Controller\Adminhtml\Comment;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Event\ManagerInterface;
use Magento\Sales\Model\Order;

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
    protected $eventManager;

    /**
     * @param Action\Context $context
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Backend\Helper\Js $jsHelper
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Backend\Helper\Js $jsHelper,
        ManagerInterface $eventManager
    ) {
        $this->adapterFactory = $adapterFactory;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->_jsHelper = $jsHelper;
        $this->eventManager = $eventManager;
        parent::__construct($context);
    }

    /**
     * Save comment action.
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data = $this->getRequest()->getPostValue()) {
            $model = $this->_objectManager->create('Eextensions\CustomOrderTab\Model\Comment');
            $model->addData($data);

            try {
                $orderId = $this->getRequest()->getParam('order_id');
                $order = $this->_objectManager->create(Order::class)->load($orderId);
                $model->save();
                // Dispatch the custom event
                $this->eventManager->dispatch('custom_order_comment_save', ['order' => $order]);
                $this->messageManager->addSuccess(__('The Comment has been submitted successfully.'));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
                }

                return $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the comment.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
        }

        return $resultRedirect->setPath('sales/order/view', ['order_id' => $this->getRequest()->getParam('order_id')]);
    }

    /**
     * Check Permission.
     *
     * @return bool
     */
    /* protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Eextensions_CustomOrderTab::comment');
    } */
}
