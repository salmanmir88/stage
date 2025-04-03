<?php

namespace Kpopia\DashboardGraph\Block\Dashboard;

use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Model\Auth\Session;

class Grids extends \Magento\Backend\Block\Dashboard\Grids
{
    protected $orderConfig;
    protected $jsonEncoder;
    protected $authSession;
    protected $orderCollectionFactory;

    public function __construct(
        Config $orderConfig,
        OrderCollectionFactory $orderCollectionFactory, // Inject Order Collection Factory
        Context $context,
        Session $authSession,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->orderConfig = $orderConfig;
        $this->orderCollectionFactory = $orderCollectionFactory; // Initialize the order collection factory
        $this->authSession = $authSession;
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        // Get all available order states dynamically
        $states = $this->orderConfig->getStates();

        // Ensure default states are included
        $defaultStates = [
            \Magento\Sales\Model\Order::STATE_NEW => __('New'),
            \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT => __('Pending Payment'),
            \Magento\Sales\Model\Order::STATE_PROCESSING => __('Processing'),
            \Magento\Sales\Model\Order::STATE_COMPLETE => __('Complete'),
            \Magento\Sales\Model\Order::STATE_CLOSED => __('Closed'),
            \Magento\Sales\Model\Order::STATE_CANCELED => __('Canceled'),
            \Magento\Sales\Model\Order::STATE_HOLDED => __('On Hold'),
            \Magento\Sales\Model\Order::STATE_PAYMENT_REVIEW => __('Payment Review')
        ];

        // Merge default states with states from the config to ensure completeness
        $states = array_merge($defaultStates, $states);
        
        // Iterate through states to create tabs dynamically
        foreach ($states as $stateCode => $stateLabel) {
            
            // Get all statuses associated with the current state
            $statuses = $this->orderConfig->getStateStatuses($stateCode);
            
            $totalOrderCount = 0;

            // Calculate the total order count by summing counts for each status
            foreach ($statuses as $statusCode => $statusLabel) {
                $orderCount = $this->orderCollectionFactory->create()
                    ->addFieldToFilter('status', $statusCode)
                    ->getSize();
                $totalOrderCount += $orderCount; // Add order count to the total for this state
            }

            // Modify the label to include the total order count for all statuses in the state
            $labelWithCount = __($stateLabel) . ' (' . $totalOrderCount . ')';

            // Create the tab for the state
            $this->addTab(
                $stateCode,
                [
                    'label' => $labelWithCount, // Use the updated label with count
                    'url' => $this->getUrl('helloworld/dashboard/magecuriouscustomtab', [
                        'state' => $stateCode,
                        '_current' => true
                    ]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );
        }
    }
}

