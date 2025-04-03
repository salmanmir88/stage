<?php

namespace Developerswing\OrderTracking\Block\Adminhtml\Order\View;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Magento\Backend\Block\Template\Context;

class TrackingLink extends \Magento\Backend\Block\Template
{
    /**
     * @var ResourceConnection
     */
    protected $resourceConnection;

    /**
     * @var Order
     */
    protected $order;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Order $order
     * @param ResourceConnection $resourceConnection
     * @param array $data
     */
    public function __construct(
        Context $context,
        ResourceConnection $resourceConnection,
        Order $order,
        array $data = []
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * Get the tracking link for the current order
     *
     * @return string|null
     */
    public function getTrackingLink()
    {
        $orderId = $this->getRequest()->getParam('order_id');
        if (!$orderId) {
            return null;
        }
        // $order = $this->order->load(($orderId));
        // $incrementId = $order->getIncrementId();
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');
        $query = $connection->select()
            ->from($tableName, ['tracking_link'])
            ->where('entity_id = :order_id');

        $result = $connection->fetchOne($query, ['order_id' => $orderId]);

        return $result ?: null;
    }
}

