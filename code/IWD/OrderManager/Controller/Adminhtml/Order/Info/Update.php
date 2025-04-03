<?php

namespace IWD\OrderManager\Controller\Adminhtml\Order\Info;

use IWD\OrderManager\Model\Order\OrderData;
use IWD\OrderManager\Controller\Adminhtml\Order\AbstractAction;
use IWD\OrderManager\Model\Log\Logger;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Class Update
 * @package IWD\OrderManager\Controller\Adminhtml\Order\Info
 */
class Update extends AbstractAction
{
    /**
     * {@inheritdoc}
     */
    const ADMIN_RESOURCE = 'IWD_OrderManager::iwdordermanager_info';

    public $scopeConfig;

    /**
     * @var OrderData
     */
    private $orderData;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param OrderRepositoryInterface $orderRepository
     * @param OrderData $orderData
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        OrderData $orderData
    ) {
        parent::__construct($context, $resultPageFactory, $orderRepository,$scopeConfig);
        $this->orderData = $orderData;
    }

    /**
     * @return null|string
     * @throws \Exception
     */
    public function getResultHtml()
    {
        $orderInfo = $this->getRequest()->getParam('order_info', []);
        $order = $this->getOrder();

        $order->setParams($orderInfo);

        Logger::getInstance()->addMessageForLevel('order_info', 'Order information was changed');

        $order->updateStatus()->save();

        return ['result' => 'reload'];
    }

    /**
     * @return OrderData
     * @throws \Exception
     */
    public function getOrder()
    {
        $orderId = $this->getOrderId();
        return $this->orderData->load($orderId);
    }
}
