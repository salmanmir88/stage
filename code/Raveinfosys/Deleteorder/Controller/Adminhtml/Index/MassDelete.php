<?php
namespace Raveinfosys\Deleteorder\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Raveinfosys\Deleteorder\Model\Remove;

class MassDelete extends Action
{
    protected $_orderCollectionFactory;
    
    protected $request;
    
    protected $filter;
    
    protected $remove;
   
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $orderCollectionFactory,
        Remove $remove
    ) {
   
        $this->filter = $filter;
        $this->request = $context->getRequest();
        $this->remove = $remove;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function getOrderCollection()
    {
        $collection = $this->_orderCollectionFactory->create();
        return $collection;
    }
 
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->filter->getCollection($this->getOrderCollection())) {
            try {
                $collection = $this->filter->getCollection($this->getOrderCollection());
                $countRemovedOrder = $this->remove->remove($collection);
                $countNonRemovedOrder = $collection->count() - $countRemovedOrder;
                if ($countRemovedOrder == 0 || $countNonRemovedOrder != $countRemovedOrder) {
                    $mod_setting_url = "Please check delete order <a href='".$this->getUrl('adminhtml/system_config/edit/section/deleteorder/', $paramsHere = ['_current'=>true])."'>configuration.</a>";
                    $this->messageManager->addError(__('Some order(s) could not be deleted. Only selected order status can be deleted. '.$mod_setting_url));
                }
                if ($countRemovedOrder) {
                    $this->messageManager->addSuccess(__('Total of %1 record were successfully deleted.', $countRemovedOrder));
                }
                return $resultRedirect->setPath('sales/order/index');
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                return $resultRedirect->setPath('sales/order/index');
            }
        } else {
            return $resultRedirect->setPath('sales/order/index');
        }
    }
}
