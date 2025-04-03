<?php

namespace Dakha\CustomWork\Ui\Component\Listing\Column\Product;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Name
 * @package Amasty\Reports\Ui\Component\Listing\Column\Product
 */
class TotalQty extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        DataPersistorInterface $dataPersistor,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataPersistor = $dataPersistor;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($item['id_field_name']) && isset($item['product_id'])) {
                    $qty = $this->getSoldProductCount($item['product_sku']);
                    $item[$this->getData('name')] = $qty;
                }
            }
        }

        return $dataSource;
    }

      /**
     * order count
     * @param $sku
     * @return array
     */
    public function getSoldProductCount($sku)
    {
        $totalSoldQty = 0;
        try {
         $collection = $this->_orderCollectionFactory->create()
          ->addFieldToSelect('*');
        
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $request = $objectManager->get('Magento\Framework\App\Request\Http');

        if(isset($request->getParam('filters')['order_status']) && $request->getParam('filters')['order_status']){
          $collection->addFieldToFilter('status',['in'=>$request->getParam('filters')['order_status']]);
        }

        /* join with payment table */
        $collection->getSelect()
        ->join(
            ["soi" => "sales_order_item"],
            'main_table.entity_id = soi.order_id',
            array('sku')
        )->where('soi.sku = ?',$sku);

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()
            ->columns([
                'qty_ordered' => 'FLOOR(SUM(soi.qty_ordered))',
                'qty_canceled' => 'FLOOR(SUM(soi.qty_canceled))',
                'qty_refunded' => 'FLOOR(SUM(soi.qty_refunded))',
                'qty_sold' => 'FLOOR(SUM(soi.qty_ordered)
                                - SUM(soi.qty_canceled) - SUM(soi.qty_refunded))'
            ]);
        
        foreach($collection as $order)
        {
            $soldQty = $order->getQtyOrdered();
            $totalSoldQty = $soldQty+$totalSoldQty;
        }
        
        return $totalSoldQty;
        
        } catch (\Exception $exception) {
            
        }
        return $totalSoldQty;

    }
}
