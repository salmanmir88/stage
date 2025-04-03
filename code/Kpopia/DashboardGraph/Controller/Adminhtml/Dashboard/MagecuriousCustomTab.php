<?php

namespace Kpopia\DashboardGraph\Controller\Adminhtml\Dashboard;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Model\Order\Config;

class MagecuriousCustomTab extends \Magento\Backend\Controller\Adminhtml\Dashboard\AjaxBlock
{
    protected $orderCollectionFactory;
    protected $_resultPageFactory;
    protected $orderConfig;

    public function __construct(
        Context $context,
        RawFactory $resultRawFactory,
        LayoutFactory $layoutFactory,
        PageFactory $resultPageFactory,
        CollectionFactory $orderCollectionFactory,
        Config $orderConfig
    ) {
        parent::__construct($context, $resultRawFactory, $layoutFactory);
        $this->_resultPageFactory = $resultPageFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderConfig = $orderConfig;
    }

    public function execute()
    {
        $state = $this->getRequest()->getParam('state');

        // Fetch the statuses associated with the current state
        $statuses = $this->orderConfig->getStateStatuses($state);

        $statusData = [];
        foreach ($statuses as $statusCode => $statusLabel) {
            // Get the count of orders for each status under this state
            $orderCount = $this->orderCollectionFactory->create()
                ->addFieldToFilter('status', $statusCode)
                ->getSize();

            // Add the status and order count to the data array
            $statusData[] = [
                'status_label' => $statusLabel,
                'order_count' => $orderCount
            ];
        }

        // Pass the data to the template file
        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Order Status Overview for State: %1', $state));

        $block = $resultPage->getLayout()
            ->createBlock('Magento\Framework\View\Element\Template')
            ->setTemplate('Kpopia_DashboardGraph::custom_file.phtml')
            ->setData('status_data', $statusData)
            ->toHtml();

        $this->getResponse()->setBody($block);
    }
}
