<?php
namespace Eextensions\AdminNameOrderComment\Observer;

class Addcommentoncancelaction implements \Magento\Framework\Event\ObserverInterface
{
  public function execute(\Magento\Framework\Event\Observer $observer)
  {

    $order= $observer->getData('order');
     //$order->doSomething();
    $authsession = \Magento\Framework\App\ObjectManager::getInstance()->create(\Magento\Backend\Model\Auth\Session::class);
    $username = $authsession->getUser()->getUsername();


    $notify = false;
    $visible = false;
    $history = $order->addStatusHistoryComment("Order canceled"." (by ".$username.")", $order->getStatus());
    $history->setIsVisibleOnFront($visible);
    $history->setIsCustomerNotified($notify);
    $history->save();

     return $this;
  }
}