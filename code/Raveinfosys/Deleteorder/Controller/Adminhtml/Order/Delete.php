<?php
namespace Raveinfosys\Deleteorder\Controller\Adminhtml\Order;

class Delete extends \Magento\Backend\App\Action
{
    protected $order;
    protected $remove;
    protected $request;
  
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\Order $order,
        \Raveinfosys\Deleteorder\Model\Remove $remove
    ) {
        $this->request = $context->getRequest();
        $this->remove = $remove;
        $this->order = $order;
        parent::__construct($context);
    }

    public function getOrder()
    {
        $orderId = $this->request->getParam("order_id");
        $order = $this->order->load($orderId, 'entity_id');
        return $order;
    }
    
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id = $this->request->getParam("order_id")) {
            $order = $this->getOrder();
            try {
                $removed = $this->remove->removeById($order);
                if ($removed) {
                    $this->messageManager->addSuccess(__('Order has been deleted successfully.'));
                } else {
                    $configUrl = $this->getUrl(
                        'adminhtml/system_config/edit/section/deleteorder/',
                        $paramsHere = ['_current' => true]
                    );
                    $this->messageManager->addError(__('Only selected order status can be deleted. Please check Delete Order <a href="' . $configUrl . '">configuration.</a>'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Order does not exist.'));
        }
        return $resultRedirect->setPath('sales/order/index');
    }
}
