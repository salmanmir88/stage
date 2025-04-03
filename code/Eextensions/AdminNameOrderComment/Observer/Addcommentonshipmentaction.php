<?php
namespace Eextensions\AdminNameOrderComment\Observer;

class Addcommentonshipmentaction implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {

    $shipment = $observer->getEvent()->getShipment();
    $order = $shipment->getOrder();
    $authsession = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Backend\Model\Auth\Session::class);
    $username = $authsession->getUser()->getUsername();


    $notify = false;
    $visible = false;
    $history = $order->addStatusHistoryComment("Shipment generated"." (by ".$username.")", $order->getStatus());
    $history->setIsVisibleOnFront($visible);
    $history->setIsCustomerNotified($notify);
    $history->save();

     return $this;
  }
}