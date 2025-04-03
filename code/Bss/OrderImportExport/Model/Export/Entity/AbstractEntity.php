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
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;

/**
 * Class AbstractEntity
 *
 * @package Bss\OrderImportExport\Model\Export\Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class AbstractEntity extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    /**
     * Current Entity Id Column
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Parent Entity Id Column
     */
    const COLUMN_PARENT_ID = 'order_id';

    /**
     * @var \Bss\OrderImportExport\Model\Export\OrderExportData
     */
    protected $currentOrderExport;

    /**
     * Prefix code for column header
     *
     * @var string
     */
    protected $prefixCode = '';

    /**
     * Table name for entity
     *
     * @var string
     */
    protected $mainTable = '';

    /**
     * Limit column for export
     *
     * @var array
     */
    protected $validColumns = [];

    /**
     * Resource Model
     *
     * @var ResourceConnection
     */
    protected $resourceModel;

    /**
     * DB connection
     *
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * Exist customer
     *
     * @var array
     */
    protected $customerEmails = [];

    /**
     * AbstractEntity constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param ResourceConnection $resource
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        ResourceConnection $resource
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory);
        $this->resourceModel = $resource;
        $this->connection = $resource->getConnection();
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
     * List of children entity
     *
     * @return array
     */
    protected function getChildren()
    {
        return [];
    }

    /**
     * Init Extract one item
     *
     * @param array $rowData
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function initExtractData($rowData)
    {
        $validColumns = $this->getValidColumns();

        if (!empty($validColumns)) {
            foreach ($rowData as $key => $value) {
                if (!in_array($key, $validColumns)) {
                    unset($rowData[$key]);
                }
            }
        }

        return $rowData;
    }

    /**
     * default valid columns
     *
     * @return array
     */
    protected function getValidColumns()
    {
        return $this->validColumns;
    }

    /**
     * @param $column
     * @return string
     */
    protected function getColumnKey($column)
    {
        return $this->prefixCode ? $this->prefixCode . ':' . $column : $column;
    }

    /**
     * Get Current Order Export Data
     *
     * @return \Bss\OrderImportExport\Model\Export\OrderExportData
     */
    public function getCurrentOrderExport()
    {
        return $this->currentOrderExport;
    }

    /**
     * Set Current Order Export Data
     *
     * @param \Bss\OrderImportExport\Model\Export\OrderExportData $orderExport
     */
    public function setCurrentOrderExport(\Bss\OrderImportExport\Model\Export\OrderExportData $orderExport)
    {
        $this->currentOrderExport = $orderExport;

        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->setCurrentOrderExport($orderExport);
            }
        }
    }

    /**
     * Retrieve All Entity Table Columns
     *
     * @return array
     */
    public function getMainTableFields()
    {
        return array_keys($this->connection->describeTable(
            $this->resourceModel->getTableName($this->mainTable)
        ));
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

        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $headerColumns = array_merge($headerColumns, $child->_getHeaderColumns());
            }
        }

        return $headerColumns;
    }

    /**
     * @param $customerId
     * @return mixed|null
     */
    protected function getCustomerEmail($customerId)
    {
        if (empty($this->customerEmails[$customerId])) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from(['e' => $this->getCustomerTable()], ['e.entity_id', 'e.email'])
                ->where('e.entity_id = :entity_id');

            $bind = [':entity_id' => $customerId];
            $rowData = $this->connection->fetchRow($select, $bind);
            if ($rowData) {
                $this->customerEmails[$customerId] = $rowData['email'];
            } else {
                $this->customerEmails[$customerId] = null;
            }
        }
        return !empty($this->customerEmails[$customerId]) ? $this->customerEmails[$customerId] : null;
    }

    /**
     * @return string
     */
    protected function getCustomerTable()
    {
        return $this->resourceModel->getTableName("customer_entity");
    }

    /**
     * Export process
     *
     * @return void
     */
    public function export()
    {
        // TODO: Implement export() method.
    }

    /**
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function exportItem($item)
    {
        // TODO: Implement exportItem() method.
    }

    /**
     * Entity type code getter
     *
     * @return void
     */
    public function getEntityTypeCode()
    {
        // TODO: Implement getEntityTypeCode() method.
    }

    /**
     * Get entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection()
    {
        // TODO: Implement _getEntityCollection() method.
    }
}
