<?php

/**
 * @author Amasty Team
 * @copyright Copyright (c) Amasty (https://www.amasty.com)
 * @package Advanced Reports Base for Magento 2
 */

namespace Kpopia\CustomWork\Model\ResourceModel\Sales\OrderItems;

use Amasty\Reports\Model\ResourceModel\Filters\AddFromFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddStoreFilter;
use Amasty\Reports\Model\ResourceModel\Filters\AddToFilter;
use Amasty\Reports\Model\Utilities\Order\GlobalRateResolver;
use Amasty\Reports\Traits\Filters;
use Amasty\Reports\Traits\ImageTrait;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\ColumnValueExpressionFactory;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends \Magento\Sales\Model\ResourceModel\Order\Collection
{
    use Filters;
    use ImageTrait;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Amasty\Reports\Helper\Data
     */
    protected $helper;

    /**
     * @var \Amasty\Reports\Model\ResourceModel\RuleIndex
     */
    protected $ruleIndex;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Attribute
     */
    private $eavAttribute;

    /**
     * @var MetadataPool
     */
    private $metadataPool;

    /**
     * @var ColumnValueExpressionFactory
     */
    private $columnValueExpressionFactory;

    /**
     * @var GlobalRateResolver
     */
    private $globalRateResolver;

    /**
     * @var AddFromFilter
     */
    private $addFromFilter;

    /**
     * @var AddToFilter
     */
    private $addToFilter;

    /**
     * @var AddStoreFilter|mixed
     */
    private $addStoreFilter;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\DB\Helper $coreResourceHelper,
        \Magento\Framework\App\RequestInterface $request, // TODO move it out of here
        \Amasty\Reports\Helper\Data $helper,
        DataPersistorInterface $dataPersistor,
        \Amasty\Reports\Model\ResourceModel\RuleIndex $ruleIndex,
        ScopeConfigInterface $scopeConfig,
        Attribute $eavAttribute,
        MetadataPool $metadataPool,
        GlobalRateResolver $globalRateResolver,
        ColumnValueExpressionFactory $columnValueExpressionFactory,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null,
        AddFromFilter $addFromFilter = null, // TODO move it to not optional
        AddToFilter $addToFilter = null,
        AddStoreFilter $addStoreFilter = null
    ) {
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $entitySnapshot,
            $coreResourceHelper,
            $connection,
            $resource
        );
        $this->request = $request;
        $this->helper = $helper;
        $this->ruleIndex = $ruleIndex;
        $this->dataPersistor = $dataPersistor;
        $this->scopeConfig = $scopeConfig;
        $this->eavAttribute = $eavAttribute;
        $this->metadataPool = $metadataPool;
        $this->columnValueExpressionFactory = $columnValueExpressionFactory;
        $this->globalRateResolver = $globalRateResolver;
        // OM for backward compatibility
        $this->addFromFilter =
            $addFromFilter ?? ObjectManager::getInstance()->get(AddFromFilter::class);
        $this->addToFilter =
            $addToFilter ?? ObjectManager::getInstance()->get(AddToFilter::class);
        $this->addStoreFilter = $addStoreFilter ?? ObjectManager::getInstance()->get(AddStoreFilter::class);
    }

    /**
     * @param AbstractCollection $collection
     */
    public function prepareCollection($collection)
    {
        $this->applyBaseFilters($collection);
        $this->joinChilds($collection);
        $this->applyToolbarFilters($collection);
    }

    /**
     * @param AbstractCollection $collection
     */
    public function joinOrderTable($collection)
    {
        $collection->getSelect()->join(
            ['sales_order' => $this->getTable('sales_order_grid')],
            'main_table.order_id = sales_order.entity_id'
        );
    }

    /**
     * @param AbstractCollection $collection
     */
    public function joinInvoiceTable($collection)
    {
        $collection->getSelect()->join(
            ['sales_invoice' => $this->getTable('sales_invoice_grid')],
            'main_table.order_id = sales_invoice.order_id'
        );
    }

    /**
     * @param AbstractCollection $collection
     */
    public function applyBaseFilters($collection)
    {
        $this->joinOrderTable($collection);
        $this->joinInvoiceTable($collection);
        $this->joinThumbnailAttribute($collection);
        $collection->getSelect()->reset(Select::COLUMNS);

        if ($this->globalRateResolver->isDefaultStore()) {
            $collection->getSelect()->join(
                ['so' => $this->getTable('sales_order')],
                'so.entity_id = main_table.order_id',
                ['base_to_global_rate']
            );
        }

        $collection->getSelect()->columns([
            'increment_id' => 'sales_order.increment_id',
            'order_status' => 'sales_order.status',
            'order_date' => 'sales_order.created_at',
            'invoice_date' => 'sales_invoice.created_at',
            'release_date' => 'main_table.release_date',
            'payment_method' => 'sales_order.payment_method',
            'product_sku' => 'main_table.sku',
            'product_name' => 'main_table.name',
            'barcode' => 'main_table.barcode',
            'total_qty' => 'main_table.total_qty',
            'category_id' => 'main_table.category_id',
            'orig_price' => $this->columnValueExpressionFactory->create([
                'expression' => $this->globalRateResolver->resolvePriceColumn('main_table.base_original_price')
            ]),
            'price' => $this->columnValueExpressionFactory->create([
                'expression' => $this->globalRateResolver->resolvePriceColumn('main_table.base_price')
            ]),
            'qty' => 'FLOOR(main_table.qty_ordered)',
            'subtotal' => sprintf(
                'IF(soi.subtotal IS NOT NULL AND soi.subtotal != 0, soi.subtotal, %s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_row_total')
            ),
            'tax' => $this->columnValueExpressionFactory->create([
                'expression' => $this->globalRateResolver->resolvePriceColumn('main_table.base_tax_amount')
            ]),
            'discounts' => sprintf(
                'IF(soi.base_discount_amount IS NOT NULL AND soi.base_discount_amount != 0, '
                    . 'soi.base_discount_amount, %s)',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_discount_amount')
            ),
            'row_total' => sprintf(
                '(IF(soi.subtotal IS NOT NULL AND soi.subtotal != 0, '
                    . 'soi.subtotal, %s) - '
                    . 'IF(soi.base_discount_amount IS NOT NULL AND soi.base_discount_amount != 0, '
                    . 'soi.base_discount_amount, %s) + '
                    . 'IF(soi.base_tax_amount IS NOT NULL AND soi.base_tax_amount != 0, '
                    . 'soi.base_tax_amount, %s))',
                $this->globalRateResolver->resolvePriceColumn('main_table.base_row_total'),
                $this->globalRateResolver->resolvePriceColumn('main_table.base_discount_amount'),
                $this->globalRateResolver->resolvePriceColumn('main_table.base_tax_amount')
            ),
            'order_id' => 'sales_order.entity_id',
            'product_id' => 'main_table.product_id',
            'thumbnail' => 'attributes.value',
        ])->where('main_table.parent_item_id IS NULL');
    }

    /**
     * @param AbstractCollection $collection
     */
    public function applyToolbarFilters($collection)
    {
        $this->addFromFilter->execute($collection);
        $this->addToFilter->execute($collection);
        $this->addStoreFilter->execute($collection, 'sales_order');
    }

    /**
     * @param AbstractCollection $collection
     */
    private function joinChilds($collection)
    {
        $childsSelect = $this->getConnection()->select()->from(
            ['soi' => $this->getTable('sales_order_item')],
            ['parent_item_id']
        )->group(
            'parent_item_id'
        );

        if ($this->globalRateResolver->isDefaultStore()) {
            $childsSelect->join(
                ['so' => $this->getTable('sales_order')],
                'soi.order_id = so.entity_id',
                []
            );
        }

        $childsSelect->columns([
            'subtotal' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_row_total')
            ),
            'base_tax_amount' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_tax_amount')
            ),
            'base_discount_amount' => sprintf(
                'SUM(%s)',
                $this->globalRateResolver->resolvePriceColumn('soi.base_discount_amount')
            ),
        ]);

        $collection->getSelect()->joinLeft(
            ['soi' => $childsSelect],
            'soi.parent_item_id = main_table.item_id',
            ''
        );
    }
}
