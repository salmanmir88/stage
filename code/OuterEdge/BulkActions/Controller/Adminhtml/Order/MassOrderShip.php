<?php

namespace OuterEdge\BulkActions\Controller\Adminhtml\Order;

use Magento\Backend\App\Action\Context;
use Magento\Sales\Model\OrderFactory as ModelOrder;
use Magento\Sales\Model\Convert\OrderFactory as ConvertOrder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Shipping\Model\ShipmentNotifier;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class MassOrderShip extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var ModelOrder
     */
    protected $orderModel;

    /**
     * @var ConvertOrder
     */
    protected $convertOrder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var ShipmentNotifier
     */
    protected $shipmentNotifier;

    /**
     * @var \Magento\Framework\DB\TransactionFactory
     */
    protected $transactionFactory;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Preparation constructor.
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param ManagerInterface $messageManager
     * @param ModelOrder $orderModel
     * @param ConvertOrder $convertOrder
     * @param ShipmentNotifier $shipmentNotifier
     * @param \Magento\Framework\DB\TransactionFactory $transactionFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        ManagerInterface $messageManager,
        ModelOrder $orderModel,
        ConvertOrder $convertOrder,
        ShipmentNotifier $shipmentNotifier,
        \Magento\Framework\DB\TransactionFactory $transactionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        parent::__construct($context, $filter);
        $this->messageManager = $messageManager;
        $this->orderModel = $orderModel;
        $this->convertOrder = $convertOrder;
        $this->shipmentNotifier = $shipmentNotifier;
        $this->collectionFactory = $collectionFactory;
        $this->transactionFactory = $transactionFactory;
        $this->scopeConfig = $scopeConfig;
    }
    
   /**
    * @param AbstractCollection $collection
    * @return \Magento\Backend\Model\View\Result\Redirect
    */
   protected function massAction(AbstractCollection $collection)
    {
         $ordersId = [];
         foreach ($collection as $order) {
                $ordersId[] = $order->getId();
          }

        $resultRedirect = $this->resultRedirectFactory->create();
        $notify = $this->getRequest()->getParam('notify');
        //$ordersId = $this->getRequest()->getParam('selected');

        if (!empty($ordersId)) {
            // Ship Order
            foreach ($ordersId as $orderId) {
               
                $order = $this->orderModel->create()->load($orderId);
                if ($order->canShip()) {

                    $convertOrder = $this->convertOrder->create();
                    $shipment = $convertOrder->toShipment($order);

                    foreach ($order->getAllItems() AS $orderItem) {
                        // Check if order item has qty to ship or is virtual
                        if (! $orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                            continue;
                        }
                        $qtyShipped = $orderItem->getQtyToShip();
                        // Create shipment item with qty
                        $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                        // Add shipment item to shipment
                        $shipment->addItem($shipmentItem);
                    }

                    // Register shipment
                    $shipment->register();
                    $shipment->getOrder()->setIsInProcess(true);
                    
                    try {
                        // Save created shipment and order
                        $transaction = $this->transactionFactory->create()->addObject($shipment)
                         ->addObject($shipment->getOrder())
                         ->save();
                         $getShipmentStatus = $this->scopeConfig->getValue('awbshipment/order/status', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                         
                         $order->setState($getShipmentStatus)->setStatus($getShipmentStatus);
                         $order->save();

                        // Send email
                        if ($notify) {
                            $this->shipmentNotifier->notify($shipment);
                            $shipment->save();
                        }
                        $this->messageManager->addSuccess(__("Shipment Succesfully Generated for order: #".$order->getIncrementId()));
                    } catch (\Exception $e) {
                        $this->messageManager->addError(__('Cannot ship order'. $e->getMessage()));
                    }

                } else {
                    $this->messageManager->addError(__("Cannot ship order, becuase It's already created or something went wrong"));
                }
            }
        }
        return $resultRedirect->setPath('sales/order/index', [], ['error' => true]);
    }
}
