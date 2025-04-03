<?php

namespace Kpopia\CustomWork\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Data extends AbstractHelper
{
    protected $_orderCollectionFactory;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        OrderCollectionFactory $orderCollectionFactory
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context);
    }

    public function getSoldProductCount($sku)
    {
        $totalSoldQty = 0;
        try {
            $collection = $this->_orderCollectionFactory->create()
                ->addFieldToSelect('*');
            
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $request = $objectManager->get('Magento\Framework\App\Request\Http');

            if (isset($request->getParam('filters')['order_status']) && $request->getParam('filters')['order_status']) {
                $collection->addFieldToFilter('status', ['in' => $request->getParam('filters')['order_status']]);
            }

            $collection->getSelect()
                ->join(
                    ["soi" => "sales_order_item"],
                    'main_table.entity_id = soi.order_id',
                    ['sku']
                )->where('soi.sku = ?', $sku);

            $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
            $collection->getSelect()
                ->columns([
                    'qty_ordered' => 'FLOOR(SUM(soi.qty_ordered))',
                    'qty_canceled' => 'FLOOR(SUM(soi.qty_canceled))',
                    'qty_refunded' => 'FLOOR(SUM(soi.qty_refunded))',
                    'qty_sold' => 'FLOOR(SUM(soi.qty_ordered)
                                - SUM(soi.qty_canceled) - SUM(soi.qty_refunded))'
                ]);

            foreach ($collection as $order) {
                $soldQty = $order->getQtyOrdered();
                $totalSoldQty += $soldQty;
            }
            
            return $totalSoldQty;

        } catch (\Exception $exception) {
            return $totalSoldQty;
        }
    }
}
