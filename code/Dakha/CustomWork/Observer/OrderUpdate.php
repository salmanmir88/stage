<?php

namespace Dakha\CustomWork\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderUpdate implements ObserverInterface
{
    protected LoggerInterface $_logger;

    protected ResourceConnection $resource;

    protected OrderFactory $order;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    private $orderRepository;

    /**
     * @param LoggerInterface $logger
     * @param ResourceConnection $resource
     */
    public function __construct(
        LoggerInterface $logger,
        ResourceConnection $resource,
        OrderFactory $order,
        InvoiceRepositoryInterface $invoiceRepository,
        ShipmentRepositoryInterface $shipmentRepository,
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->_logger = $logger;
        $this->resource = $resource;
        $this->order = $order;
        $this->invoiceRepository = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderStatusHistory = $observer->getData('data_object');
        if ($order) {
            $orderId = $order->getId();
        } elseif ($orderStatusHistory) {
            $orderId = $orderStatusHistory->getParentId();
        }
        // Get the connection instance
        $connection = $this->resource->getConnection();
        $salesOrderGridTable = $this->resource->getTableName('sales_order_grid');
        $salesOrderItemTable = $this->resource->getTableName('sales_order_item');
        // Prepare the updated timestamp value
        $updatedAt = (new \DateTime())->format('Y-m-d H:i:s');
        // Update the updated_at field in sales_order_grid
        $connection->update(
            $salesOrderGridTable,
            ['last_updated_at' => $updatedAt],
            ['entity_id = ?' => $orderId]
        );

        $shipmentDate = $this->getShipmentDataByOrderId($orderId);
        foreach ($shipmentDate as $shipment) {
            $shipmentDate = $shipment->getCreatedAt();
        }
        $invoiceDate = $this->getInvoiceDataByOrderId($orderId);
        foreach ($invoiceDate as $invoice) {
            $invoiceDate = $invoice->getCreatedAt();
        }

        if ($invoiceDate) {
            $connection->update(
                $salesOrderGridTable,
                ['invoice_date' => $invoiceDate],
                ['entity_id = ?' => $orderId]
            );
        }
        if ($shipmentDate) {
            $connection->update(
                $salesOrderGridTable,
                ['shipment_date' => $shipmentDate],
                ['entity_id = ?' => $orderId]
            );
        }
    }
    /**
     * Get Shipment data by Order Id
     *
     * @param int $orderId
     * @return ShipmentInterface[]|null
     */
    public function getShipmentDataByOrderId(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $shipment = $this->shipmentRepository->getList($searchCriteria);
            $shipmentRecords = $shipment->getItems();
        } catch (Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            $shipmentRecords = null;
        }
        return $shipmentRecords;
    }
    /**
     * Get Invoice data by Order Id
     *
     * @param int $orderId
     * @return InvoiceInterface[]|null
     */
    public function getInvoiceDataByOrderId(int $orderId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('order_id', $orderId)->create();
        try {
            $Invoice = $this->invoiceRepository->getList($searchCriteria);
            $InvoiceRecords = $Invoice->getItems();
        } catch (Exception $exception) {
            $this->_logger->critical($exception->getMessage());
            $InvoiceRecords = null;
        }
        return $InvoiceRecords;
    }
}
