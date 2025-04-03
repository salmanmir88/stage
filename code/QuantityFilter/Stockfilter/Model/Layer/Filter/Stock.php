<?php

namespace QuantityFilter\Stockfilter\Model\Layer\Filter;

class Stock extends \Magento\Catalog\Model\Layer\Filter\AbstractFilter
{
    const IN_STOCK_COLLECTION_FLAG = 'stock_filter_applied';

    protected $_activeFilter = false;
    protected $_requestVar = 'in-stock';
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Layer\Filter\ItemFactory $filterItemFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Layer $layer,
        \Magento\Catalog\Model\Layer\Filter\Item\DataBuilder $itemDataBuilder,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($filterItemFactory, $storeManager, $layer, $itemDataBuilder, $data);
    }

    public function apply(\Magento\Framework\App\RequestInterface $request)
    {
        $filter = $request->getParam($this->getRequestVar(), null);
        if (is_null($filter)) {
            return $this;
        }
        $this->_activeFilter = true;
        $filter = (int)(bool)$filter;
        $collection = $this->getLayer()->getProductCollection();
        $collection->setFlag(self::IN_STOCK_COLLECTION_FLAG, true);
        $collection->getSelect()->where('stock_status_index.stock_status = ?', $filter);
        $this->getLayer()->getState()->addFilter(
            $this->_createItem($this->getLabel($filter), $filter)
        );
        return $this;
    }

    public function getName()
    {
        return __("Stock");
    }

    protected function _getItemsData()
    {
        if ($this->getLayer()->getProductCollection()->getFlag(self::IN_STOCK_COLLECTION_FLAG)) {
            return [];
        }

        $data = [];
        foreach ($this->getStatuses() as $status) {
            $data[] = [
                'label' => $this->getLabel($status),
                'value' => $status,
                'count' => $this->getProductsCount($status)
            ];
        }

        return $data;
    }

    public function getStatuses()
    {
        return [
            \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK,
            \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK
        ];
    }

    public function getLabels()
    {
        return [
            \Magento\CatalogInventory\Model\Stock::STOCK_IN_STOCK => __('In Stock'),
            \Magento\CatalogInventory\Model\Stock::STOCK_OUT_OF_STOCK => __('Out of stock'),
        ];
    }

    public function getLabel($value)
    {
        $labels = $this->getLabels();
        if (isset($labels[$value])) {
            return $labels[$value];
        }
        return '';
    }

    public function getProductsCount($value)
    {
        $category = $this->getLayer()->getCurrentCategory(); // Get current category

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $collectionFactory = $objectManager->create(\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory::class);

        $collection = $collectionFactory->create();
        $collection->addCategoryFilter($category); // Apply category filter

        // Join stock status table to filter by stock status correctly
        $collection->getSelect()->join(
            ['stock_status' => 'cataloginventory_stock_status'],
            'stock_status.product_id = e.entity_id',
            []
        )->where('stock_status.stock_status = ?', (int)$value);

        return $collection->getSize(); // Get the correct product count for stock status
    }
}
