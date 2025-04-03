<?php
namespace Raveinfosys\Deleteorder\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Api\OrderRepositoryInterface;

class Remove extends AbstractModel
{
    protected $helper;
    protected $orderRepository;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        \Raveinfosys\Deleteorder\Helper\Data $helper
    ) {
        $this->helper = $helper;
        $this->orderRepository = $orderRepository;
    }

    public function remove($collection)
    {
        $countOrder = 0;
        foreach ($collection as $order) {
            $result = $this->removeById($order);
            if ($result) {
                $countOrder++;
            }
        }
        return $countOrder;
    }

    public function removeById($order)
    {
        $result = false;
        $allowedOrderStatus = $this->getAllowedOrderStatus();
        if (in_array($order->getStatus(), $allowedOrderStatus)) {
            if ($order->hasInvoices()) {
                $order->getInvoiceCollection()->walk('delete');
            }

            if ($order->hasShipments()) {
                $order->getShipmentsCollection()->walk('delete');
            }

            if ($order->hasCreditmemos()) {
                $order->getCreditmemosCollection()->walk('delete');
            }
            $result = $this->orderRepository->delete($order);
        }
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    public function getAllowedOrderStatus()
    {
        return $this->helper->getAllowedOrderStatus();
    }
}
