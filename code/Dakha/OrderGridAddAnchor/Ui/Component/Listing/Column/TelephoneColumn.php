<?php

namespace Dakha\OrderGridAddAnchor\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class TelephoneColumn extends Column
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

        // Collect unique telephone numbers
        $telephones = array_column($dataSource['data']['items'], 'telephone');
        $telephoneCounts = $this->getTelephoneCounts($telephones);

        foreach ($dataSource['data']['items'] as &$item) {
            $telephone = $item['telephone'];
            if (isset($telephoneCounts[$telephone]) && $telephoneCounts[$telephone] > 1) {
                $telephone = '<span style="background-color: #90EE90;">' . $telephone . '</span>';
            }
            $item[$this->getData('name')] = $telephone;
        }

        return $dataSource;
    }

    private function getTelephoneCounts(array $telephones)
    {
        // Load orders in a single query
        $orders = $this->orderCollectionFactory->create()
            ->addAttributeToSelect('entity_id') // Select the order ID
            ->join(
                ['soa' => 'sales_order_address'],
                'main_table.entity_id = soa.parent_id AND soa.address_type = \'billing\'',
                ['telephone'] // Select the telephone from sales_order_address
            )
            ->addFieldToFilter('soa.telephone', ['in' => $telephones])
            ->load();

        // Count occurrences of each telephone number
        $telephoneCounts = [];
        foreach ($orders as $order) {
            $telephone = $order->getData('telephone'); // Use getData to fetch the telephone
            $telephoneCounts[$telephone] = isset($telephoneCounts[$telephone]) ? $telephoneCounts[$telephone] + 1 : 1;
        }

        return $telephoneCounts;
    }
}
