<?php
/**
 * Copyright © OrderTracking All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Developerswing\OrderTracking\Observer\Sales;

class OrderSaveAfter implements \Magento\Framework\Event\ObserverInterface
{
    protected $orderstatusdateFactory;
    private $logger;
    public function __construct(
        \Developerswing\OrderTracking\Model\OrderstatusdateFactory $orderstatusdateFactory,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->orderstatusdateFactory = $orderstatusdateFactory;
        $this->logger                 = $logger;   
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
       $order = $observer->getEvent()->getOrder();
         
       try {
         
	         $collection = $this->orderstatusdateFactory->create()->getCollection()
	                            ->addFieldToFilter('order_id',$order->getId())->getFirstItem();
	         if($collection->getId())
	         {
	           if(count($order->getShipmentsCollection())>0)
	           { 
	           	 $shipmentDate = date('d/m/Y'); 
	           	 foreach($order->getShipmentsCollection() as $shipment){
			       $shipmentDate = date('d/m/Y', strtotime($shipment->getCreatedAt()));
			     }
	             $collection->setShippingStatus($shipmentDate);
	             $collection->save(); 	
	           }
	           
	           if($order->getStatus()=='shipment')
	           {
	             $collection->setWithCourierStatus(date('Y-m-d h:i:s'));
	             $collection->save();
	             $order->setPreOrder(0);
	             $order->save(); 
	           }

	           if($order->getStatus()=='international_shipment')
	           {
	             $collection->setDeliveredStatus(date('Y-m-d h:i:s'));
	             $collection->save();
	           }
	           if($order->getStatus()=='ship_via_courier')
	           {
	           	 $collection->setOrderId($order->getId());
	           	 $collection->setPreOrder(1);
	             $collection->save();
	           }
	           if($order->getStatus()=='denied')
	           {
	           	 $collection->setOrderId($order->getId());
	           	 $collection->setProcessingStatusDate(date('Y-m-d h:i:s', strtotime($order->getUpdatedAt())));
	             $collection->save();
	           }
	           $orderStatusArr = ['shipping_mecca','processing','transit'];
	           if($collection->getPreOrder()&&in_array($order->getStatus(), $orderStatusArr))
	           {
	           	 $collection->setPreorderChangeStatusDate(date('Y-m-d h:i:s', strtotime($order->getUpdatedAt())));
                 $collection->save();
	           }
	           
	           if($order->getState()=='pending')
	           {
                   $collection->setPreOrder(0);
	               $collection->save();
	               $order->setPreOrder(0);
                   $order->save();
	           }

	          if($order->getState()=='new')
	           {
                   $collection->setPreOrder(0);
	               $collection->save();
	               $order->setPreOrder(0);
                   $order->save();
	           }
               
	           if($order->getState()=='processing' && $order->getStatus()=='fetchr_held')
	           {
	           	 $collection->setOrderId($order->getId());
	           	 $collection->setPreOrder(0);
	           	 $collection->setProcessingStatusDate(date('Y-m-d h:i:s', strtotime($order->getUpdatedAt())));
	           	 $collection->setPreorderChangeStatusDate('');
                 $collection->save();
                 $order->setPreOrder(0);
                 $order->save();
	           }
               
	           if($order->getStatus()=='processing')
	           {
	           	 $collection->setOrderId($order->getId());
	           	 $collection->setPreOrder(0);
	           	 $collection->setPreorderChangeStatusDate('');
                 $collection->save();
                 $order->setPreOrder(0);
                 $order->save();
	           }


	         }else{
	           $model = $this->orderstatusdateFactory->create();	
	           if(count($order->getShipmentsCollection())>0)
	           {
	           	 $shipmentDate = date('d/m/Y'); 
	           	 foreach($order->getShipmentsCollection() as $shipment){
			       $shipmentDate = date('d/m/Y', strtotime($shipment->getCreatedAt()));
			     }
	           	 $model->setOrderId($order->getId());
	             $model->setShippingStatus($shipmentDate);
	             $model->save(); 	
	           }

	           if($order->getStatus()=='shipment')
	           {
	           	 $model->setOrderId($order->getId());
	             $model->setWithCourierStatus(date('Y-m-d h:i:s'));
	             $model->save();
	           }
	           
	           if($order->getStatus()=='international_shipment')
	           {
	           	 $model->setOrderId($order->getId());
	             $model->setDeliveredStatus(date('Y-m-d h:i:s'));
	             $model->save();
	           }
	           if($order->getStatus()=='ship_via_courier')
	           {
	           	 $model->setOrderId($order->getId());
	           	 $model->setPreOrder(1);
	             $model->save();
	           }
	           if($order->getStatus()=='denied')
	           {
	           	 $model->setOrderId($order->getId());
	           	 $model->setProcessingStatusDate(date('Y-m-d h:i:s', strtotime($order->getUpdatedAt())));
	             $model->save();
	           }
	           
	           if($order->getState()=='pending')
	           {
                 $model->setOrderId($order->getId());
	           	 $model->setPreOrder(0);
                 $model->save();
                 $order->setPreOrder(0);
                 $order->save();
	           }

	           if($order->getState()=='new')
	           {
                 $model->setOrderId($order->getId());
	           	 $model->setPreOrder(0);
                 $model->save();
                 $order->setPreOrder(0);
                 $order->save();
	           }

	           if($order->getState()=='processing' && !$order->getStatus()=='processing')
	           {
	           	 $model->setOrderId($order->getId());
	           	 $model->setPreOrder(0);
	           	 $model->setProcessingStatusDate(date('Y-m-d h:i:s', strtotime($order->getUpdatedAt())));
	           	 $model->setPreorderChangeStatusDate('');
                 $model->save();
                 $order->setPreOrder(0);
                 $order->save();
	           }

	           if($order->getStatus()=='processing')
	           {
	           	 $model->setOrderId($order->getId());
	           	 $model->setPreorderChangeStatusDate('');
	           	 $model->setPreOrder(0);
                 $model->save();
                 $order->setPreOrder(0);
                 $order->save();
	           }

	         }
	      } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
         }                    

    }
}