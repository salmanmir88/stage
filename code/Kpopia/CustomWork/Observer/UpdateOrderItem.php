<?php

namespace Kpopia\CustomWork\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateOrderItem implements ObserverInterface
{
    protected $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderId = $order->getId();

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order_item');

        $sql = "
            UPDATE $tableName AS item
            JOIN (
                SELECT sku, 
                       SUM(qty_ordered) AS total_qty_ordered,
                       SUM(qty_canceled) AS total_qty_canceled,
                       SUM(qty_refunded) AS total_qty_refunded
                FROM $tableName
                WHERE order_id = :order_id
                GROUP BY sku
            ) AS totals ON item.sku = totals.sku AND item.order_id = :order_id
            SET item.total_qty = (totals.total_qty_ordered - totals.total_qty_canceled - totals.total_qty_refunded)
        ";

        $bind = [':order_id' => $orderId];
        $connection->query($sql, $bind);
        $this->updateReleaseDate($order, $connection, $tableName);
    }

    public function updateReleaseDate($order, $connection, $salesOrderItemTable)
    {
        $orderItems = $order->getItems();
        foreach ($orderItems as $item) {
            $product = $item->getProduct();
            $sku = $product->getSku();
            $releaseDate = $product->getReleaseDate();
            if ($releaseDate) {
                $connection->update(
                    $salesOrderItemTable,
                    ['release_date' => $releaseDate],
                    ['sku = ?' => $sku]
                );
            }
        }
    }
}
