<?php

namespace Dakha\CustomWork\Observer;

use Exception;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\App\ResourceConnection;

class OrderPlace implements ObserverInterface
{
    protected const PHRASE_ENTITY = 'order_place_event';

    protected LoggerInterface $_logger;

    protected ResourceConnection $resource;

    /**
     * @param LoggerInterface $logger
     * @param ResourceConnection $resource
     */
    public function __construct(
        LoggerInterface $logger,
        ResourceConnection $resource
    ) {
        $this->_logger = $logger;
        $this->resource = $resource;
    }
    
    public function execute(Observer $observer)
    {
        try {
            /** @var \Magento\Sales\Model\Order $order */
            $order = $observer->getEvent()->getOrder();
            $payment = $order->getPayment();
            $paymentMethod = $payment->getMethod();
            if($paymentMethod=='myfatoorah_gateway' || $paymentMethod=='myfatoorah_gatewaydummy'){
               $order->setShippingMethod('logistiqshipping_logistiqshipping')
                     ->setCourier('logistiq')
                     ->save();
               $this->updateOrderGridCourier($order,'logistiq');       
            }else{
               $order->setCourier('saee')
                     ->save();
               $this->updateOrderGridCourier($order,'saee');       
            }
            $this->_logger->info("Order place event. Order id=" . $order->getId());
        } catch (Exception $e) {
            $this->_logger->critical('Error '.$e->getMessage());
        }
    }

    public function updateOrderGridCourier($order,$courier)
    {
       $connection  = $this->resource->getConnection();
       $data = ["courier"=>$courier];
       $id = $order->getId();
       $where = ['entity_id = ?' => (int)$id];

       $tableName = $connection->getTableName("sales_order_grid");
       $connection->update($tableName, $data, $where);
    }
}
