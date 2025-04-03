<?php

namespace Kpopia\Filter\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Psr\Log\LoggerInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class UpdateMainStatus implements ObserverInterface
{
    protected $resource;
    protected $logger;
    protected $orderConfig;

    public function __construct(
        ResourceConnection $resource,
        LoggerInterface $logger,
        OrderConfig $orderConfig
    ) {
        $this->resource = $resource;
        $this->logger = $logger;
        $this->orderConfig = $orderConfig;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if (!$order instanceof Order) {
            return;
        }

        $orderId = $order->getEntityId();
        $orderStatus = $order->getStatus();
        $connection = $this->resource->getConnection();
        $statusStateTable = $this->resource->getTableName('sales_order_status_state');
        $orderTable = $this->resource->getTableName('sales_order_grid');
        $states = $this->orderConfig->getStates();
        // Retrieve state based on the status from the sales_order_status_state table
        $select = $connection->select()
            ->from($statusStateTable, ['state'])
            ->where('status = ?', $orderStatus)
            ->limit(1);
        $orderState = $connection->fetchOne($select);
        foreach ($states as $state => $stateLabel) {
            if ($orderState == $state) {
                $orderState = (string)$stateLabel;
            }
        }
        if ($orderState) {
            try {
                // Update the main_status column in the sales_order_grid table
                $connection->update(
                    $orderTable,
                    ['main_status' => $orderState],
                    ['entity_id = ?' => $orderId]
                );
                $this->logger->info("Order ID $orderId updated with main_status: $orderState");
            } catch (\Exception $e) {
                $this->logger->error("Error updating Order ID $orderId: " . $e->getMessage());
            }
        } else {
            $this->logger->info("No state found for order status: $orderStatus for Order ID $orderId");
        }
    }
}
