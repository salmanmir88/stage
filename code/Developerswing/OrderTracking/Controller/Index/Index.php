<?php

declare(strict_types=1);

namespace Developerswing\OrderTracking\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Sales\Model\OrderFactory;

class Index extends Action
{
    protected $resultPageFactory;
    protected $redirectFactory;
    protected $orderFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RedirectFactory $redirectFactory
     * @param OrderFactory $orderFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        RedirectFactory $redirectFactory,
        OrderFactory $orderFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->redirectFactory = $redirectFactory;
        $this->orderFactory = $orderFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $orderId = $this->getRequest()->getParam('order_id');

        if ($orderId) {
            $orderId = trim($orderId);
            // Validate the order ID
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);

            if ($order && $order->getId()) {
                // Proceed to load the page with the order ID
                $resultPage = $this->resultPageFactory->create();
                $resultPage->getConfig()->setDescription(
                    "Track your order or shipment. Thank you for shopping online with Kpopia shop! Order ID: {$orderId}"
                );
                return $resultPage;
            } else {
                // Redirect to ordertrack page with error message
                $this->messageManager->addErrorMessage(__('Order not found or invalid ID 12'));
                return $this->redirectFactory->create()->setPath('ordertrack');
            }
        }

        // Default behavior: Render the tracking page without specific order
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->setDescription(
            "Track your order or shipment. Thank you for shopping online with Kpopia shop! Enter order ID to track the status of order. Easy returns & free shipping on orders."
        );
        return $resultPage;
    }
}

