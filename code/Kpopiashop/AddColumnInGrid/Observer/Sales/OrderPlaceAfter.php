<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kpopiashop\AddColumnInGrid\Observer\Sales;

class OrderPlaceAfter implements \Magento\Framework\Event\ObserverInterface
{
    
    protected $_productRepository;
    protected $orderRepository;
    protected $_resource;
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\ResourceConnection $resource
    )
    {
        $this->_productRepository = $productRepository;
        $this->orderRepository = $orderRepository; 
        $this->_resource = $resource;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
             $order_ids = $observer->getEvent()->getOrderIds()[0];
             $order = $this->orderRepository->get($order_ids);
             $connection = $this->_resource->getConnection();
             $tableName = $this->_resource->getTableName('sales_order_item');
             $orderItems = $order->getAllItems();
             foreach($orderItems as $item){ 
                     $_product = $this->_productRepository->getById($item->getProductId());
                     if($_product->getBarcode()) 
                       {
                          $sql = "UPDATE " . $tableName . " SET barcode = ".$_product->getBarcode(). " WHERE item_id = " . $item->getItemId();
                          $connection->query($sql);
                       }
             }
            } catch (\Exception $e) {
                    
          }
    }
}