<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderImportExport\Model\Export\Entity;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Class DownloadLink
 *
 * @package Bss\OrderImportExport\Model\Export\Entity
 */
class DownloadLink extends AbstractEntity
{
    /**
     * Current Entity Id Column
     */
    const COLUMN_ENTITY_ID = 'purchased_id';

    /**
     * Parent Entity Id Column
     */
    const COLUMN_PARENT_ID = 'order_id';

    /**
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_DOWNLOAD_LINK;

    /**
     * Table name for entity
     *
     * @var string
     */
    protected $mainTable = 'downloadable_link_purchased';

    /**
     * @var DownloadLink\Item
     */
    protected $itemEntity;

    /**
     * Tax constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param ResourceConnection $resource
     * @param DownloadLink\ItemFactory $itemFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        ResourceConnection $resource,
        DownloadLink\ItemFactory $itemFactory
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $resource);
        $this->itemEntity = $itemFactory->create();
    }

    /**
     * List of children entity
     *
     * @return array
     */
    protected function getChildren()
    {
        return [
            $this->itemEntity
        ];
    }

    /**
     * Extract Data From Parent Object
     *
     * @param \Magento\Framework\Model\AbstractModel $parent
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function extractData($parentIds)
    {
        if ($this->mainTable) {
            if (!is_array($parentIds)) {
                $parentIds = [$parentIds];
            }
            $rowIndex = 0;
            $entityIds = [];

            $select = $this->connection->select()->from(
                $this->resourceModel->getTableName($this->mainTable),
                $this->validColumns ? : '*'
            )->where(
                static::COLUMN_PARENT_ID . ' IN (?)',
                $parentIds
            );
            $result = $this->connection->query($select);

            while ($row = $result->fetch()) {
                $itemRow = [];
                $entityIds[] = $row[static::COLUMN_ENTITY_ID];
                $row = $this->initExtractData($row);
                if (!empty($row['customer_id'])) {
                    $row['customer_email'] = $this->getCustomerEmail($row['customer_id']);
                } else {
                    $row['customer_email'] = '';
                }

                foreach ($row as $column => $value) {
                    $columnKey = $this->getColumnKey($column);
                    $itemRow[$columnKey] = $value;
                }

                $currentOrderExport = $this->getCurrentOrderExport();
                $rowData = $currentOrderExport->getData($rowIndex);
                $currentOrderExport->addRow(array_merge($rowData, $itemRow), $rowIndex);
                $rowIndex++;
            }

            if ($this->getChildren()) {
                foreach ($this->getChildren() as $child) {
                    $child->extractData($entityIds);
                }
            }
        }
    }

    /**
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
    {
        $headerColumns = [];
        $maintableColumns = $this->getMainTableFields();
        foreach ($maintableColumns as $column) {
            $headerColumns[] = $this->prefixCode . ":" . $column;
        }
        $headerColumns = array_merge($headerColumns, [$this->prefixCode . ":" . 'customer_email']);
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $headerColumns = array_merge($headerColumns, $child->_getHeaderColumns());
            }
        }

        return $headerColumns;
    }
}
