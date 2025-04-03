<?php 
namespace Evincemage\PayTabsCancel\Plugin;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\StatusResolver;

class AuthorizeCommandPlugin
{
	public function aroundExecute(\Magento\Sales\Model\Order\Payment\AuthorizeCommand $subject,OrderPaymentInterface $payment, $amount, OrderInterface $order,callable $proceed)
	{
		echo "string this";exit;
		$result = $proceed($payment, $amount, $order);
		if($payment->getIsTransactionPending())
		{
			$state = Order::STATE_CANCELED;
			$order->setState($state);
			$order->save();
		}
		
		return $result;
	}

}