<?php

namespace Kpopia\Filter\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Backend\Model\Auth\Session as AuthSession;
use Magento\Sales\Model\Order;

class MassShip extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    protected $orderManagement;
    protected $authSession;
    protected $collectionFactory;

    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        OrderManagementInterface $orderManagement,
        AuthSession $authSession
    ) {
        parent::__construct($context, $filter);
        $this->authSession = $authSession;
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
    }

    protected function massAction(AbstractCollection $collection)
    {
        $countShipOrder = 0;
        $nonShipOrderNumbers = [];

        $username = $this->authSession->getUser()->getUsername();
        $appendUsername = "(" . $username . ")";

        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }

            // Load order and related collections
            $loadedOrder = $this->_objectManager->create('Magento\Sales\Model\Order')->load($order->getEntityId());
            $shipments = $loadedOrder->getShipmentsCollection(); // Get all shipments
            $invoices = $loadedOrder->getInvoiceCollection(); // Get all invoices

            $hasShipment = count($shipments) > 0;
            $hasInvoice = count($invoices) > 0;

            // If both shipment and invoice already exist, skip this order
            if ($hasShipment && $hasInvoice || $hasShipment) {
                $nonShipOrderNumbers[] = $order->getIncrementId();
                continue;
            }

            // If the order has no shipment and no invoice, create both
            if (!$hasInvoice && !$hasShipment) {
                try {
                    $this->createInvoiceAndShipment($loadedOrder, $appendUsername);
                    $countShipOrder++;
                } catch (\Exception $e) {
                    $this->messageManager->addError(__("Error creating shipment and invoice for order %1: %2", $order->getIncrementId(), $e->getMessage()));
                }
                continue;
            }

            // If the order has an invoice but no shipment, create a shipment
            if ($hasInvoice && !$hasShipment) {
                try {
                    $this->createShipment($loadedOrder, $appendUsername);
                    $countShipOrder++;
                } catch (\Exception $e) {
                    $this->messageManager->addError(__("Error creating shipment for order %1: %2", $order->getIncrementId(), $e->getMessage()));
                }
                continue;
            }
        }

        // Success and error messages
        if ($countShipOrder > 0) {
            $this->messageManager->addSuccess(__('%1 order(s) shipment(s) created successfully.', $countShipOrder));
        }

        if (count($nonShipOrderNumbers) > 0) {
            $this->messageManager->addError(__('Shipment already created for order(s): %1', implode(', ', $nonShipOrderNumbers)));
        }

        // Redirect back to the grid page
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

    /**
     * Create both invoice and shipment for the order
     */
    private function createInvoiceAndShipment($order, $appendUsername)
    {
        // Create invoice
        $invoice = $order->prepareInvoice();
        $invoice->register();
        $invoice->save();

        // Create shipment
        $this->createShipment($order, $appendUsername);

        // Change order status to 'processing' after invoice
        $order->setState(Order::STATE_PROCESSING);
        $order->setStatus('processing');
        $order->addStatusToHistory('processing', 'Invoice created and order moved to processing: ' . $appendUsername);
        $order->save();
    }

    /**
     * Create a shipment for the order
     */
    private function createShipment($order, $appendUsername)
    {
        $convertOrder = $this->_objectManager->create('Magento\Sales\Model\Convert\Order');
        $shipment = $convertOrder->toShipment($order);

        foreach ($order->getAllItems() as $orderItem) {
            if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                continue;
            }
            $qtyShipped = $orderItem->getQtyToShip();
            $shipmentItem = $convertOrder->itemToShipmentItem($orderItem)->setQty($qtyShipped);
            $shipment->addItem($shipmentItem);
        }

        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);

        try {
            $shipment->save();
            $shipment->getOrder()->save();

            // Adding order status and history
            $order->addStatusHistoryComment(__('Notified customer about shipment #%1. ' . $appendUsername, $shipment->getId()))
                ->setIsCustomerNotified(true)->save();

            foreach ($order->getItemsCollection()->addAttributeToSelect('*') as $item) {
                if (!$item->getQtyToShip() || $item->getIsVirtual()) {
                    continue;
                }
                $item->setQtyShipped($item->getQtyToShip());
                $item->save();
            }

            $this->_objectManager->create('Magento\Shipping\Model\ShipmentNotifier')
                ->notify($shipment);

            // After shipment creation, change order status to 'complete'
            $order->setState(Order::STATE_COMPLETE);
            $order->setStatus('complete');
            $order->addStatusToHistory('complete', 'Order status set to complete after shipment: ' . $appendUsername);
            $order->save();
        } catch (\Exception $e) {
            $this->messageManager->addError(__("Error creating shipment for order %1: %2", $order->getIncrementId(), $e->getMessage()));
        }
    }
}
