<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Kpopia\CustomWork\Model\ResourceModel\Sales\OrderItems\Grid;

use Magento\Framework\Data\Collection\Db\FetchStrategyInterface as FetchStrategy;
use Magento\Framework\Data\Collection\EntityFactoryInterface as EntityFactory;
use Magento\Framework\Event\ManagerInterface as EventManager;
use Psr\Log\LoggerInterface as Logger;

class Collection extends \Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult
{
    /**
     * @var array
     */
    private $map = [
        'product_sku' => 'main_table.sku',
        'product_name' => 'main_table.name',
        'order_date' => 'sales_order.created_at',
        'invoice_date' => 'sales_invoice.created_at',
        'release_date' => 'main_table.release_date',
        'total_qty' => 'main_table.total_qty',
        'order_status' => 'sales_order.status',
        'barcode' => 'main_table.barcode',
        'category_id' => 'main_table.category_id',
    ];

    public function __construct(
        EntityFactory $entityFactory,
        Logger $logger,
        FetchStrategy $fetchStrategy,
        EventManager $eventManager,
        \Kpopia\CustomWork\Model\ResourceModel\Sales\OrderItems\Collection $filterApplier,
        $mainTable = 'sales_order_item',
        $resourceModel = \Kpopia\CustomWork\Model\ResourceModel\Sales\OrderItems\Collection::class
    ) {
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $mainTable, $resourceModel);

        $filterApplier->prepareCollection($this);
    }

    protected function _construct()
    {
        parent::_construct();
        foreach ($this->map as $alias => $field) {
            $this->addFilterToMap(
                $alias,
                $field
            );
        }
    }
}
