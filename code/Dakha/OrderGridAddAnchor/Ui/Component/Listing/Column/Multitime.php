<?php

namespace Dakha\OrderGridAddAnchor\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Multitime extends Column
{
    protected $orderCollectionFactory;

    public function __construct(
        CollectionFactory $orderCollectionFactory,
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        // Collect unique customer emails
        $emails = array_column($dataSource['data']['items'], 'customer_email');
        $emailCounts = $this->getEmailCounts($emails);

        foreach ($dataSource['data']['items'] as &$item) {
            $email = $item['customer_email'];
            if (isset($emailCounts[$email]) && $emailCounts[$email] > 1) {
                $email = '<span style="background-color: #90EE90;">' . $email . '</span>';
            }
            $item[$this->getData('name')] = $email;
        }

        return $dataSource;
    }

    private function getEmailCounts(array $emails)
    {
        // Load orders in a single query
        $orders = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('customer_email')
            ->addAttributeToFilter('customer_email', ['in' => $emails])
            ->load();

        // Count occurrences of each email
        $emailCounts = [];
        foreach ($orders as $order) {
            $email = $order->getCustomerEmail();
            $emailCounts[$email] = isset($emailCounts[$email]) ? $emailCounts[$email] + 1 : 1;
        }

        return $emailCounts;
    }
}
