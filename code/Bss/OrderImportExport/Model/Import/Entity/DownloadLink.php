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
namespace Bss\OrderImportExport\Model\Import\Entity;

use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Downloadable Link Purchased Import
 */
class DownloadLink extends AbstractEntity
{
    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'purchased_id';
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'downloadLinkEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateDownloadLinkEntityId';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'downloadLinkEntityIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Download Link purchased_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Download Link purchased_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Download Link purchased_id is not exist',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'downloadable_link_purchased';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'purchased_id',
        'order_item_id',
        'customer_email',
        'link_section_title'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'purchased_id',
        'order_item_id',
        'customer_email',
        'link_section_title'
    ];

    /**
     * Custom Csv Column For Entity
     *
     * @var array
     */
    protected $customColumns = [
        'customer_email'
    ];

    /**
     * All columns has base prefix on database
     *
     * @var array
     */
    protected $baseFields = [];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_DOWNLOAD_LINK;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\DownloadLink\Item
     */
    protected $downloadLinkItem;

    /**
     * Order Store Id
     *
     * @var int
     */
    protected $orderStoreId = 0;

    /**
     * DownloadLink constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping
     * @param \Bss\OrderImportExport\Helper\Sequence $sequenceHelper
     * @param DownloadLink\ItemFactory $downloadLinkItemFactory
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping,
        \Bss\OrderImportExport\Helper\Sequence $sequenceHelper,
        \Bss\OrderImportExport\Model\Import\Entity\DownloadLink\ItemFactory $downloadLinkItemFactory
    ) {
        $this->downloadLinkItem = $downloadLinkItemFactory->create();
        parent::__construct(
            $jsonHelper,
            $importExportData,
            $importData,
            $resource,
            $resourceHelper,
            $errorAggregator,
            $mapping,
            $sequenceHelper
        );
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        return [
            $this->downloadLinkItem
        ];
    }

    /**
     * Import Data
     *
     * @return bool
     */
    public function importData()
    {
        parent::importData();

        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $purchasedIds = $this->getDownloadLinkIdsMapped();
                $orderIds = $this->getOrderIdsMapped();
                $orderItemIds = $this->getItemIdsMapped();
                $child->setOrderIdsMapped($orderIds);
                $child->setItemIdsMapped($orderItemIds);
                $child->setDownloadLinkIdsMapped($purchasedIds);
                $child->importData();
            }
        }

        return true;
    }

    /**
     * Retrieve Data For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     */
    public function extractRowData(array $rowData, $rowNumber = 0)
    {
        $rowData = parent::extractRowData($rowData);
        if (isset($rowData[self::COLUMN_STORE_ID])) {
            $this->orderStoreId = $rowData[self::COLUMN_STORE_ID];
        }
        $rowData = $this->extractFields($rowData, $this->prefixCode);
        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    /**
     * Prepare Data For Add
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     */
    protected function prepareDataToAdd(array $rowData, $rowNumber)
    {
        $entitiesToCreate = [];

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }
        $orderItemId = $this->getOrderItemId($rowData);

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;
        $this->downloadLinkIdsMapped[$this->getEntityId($rowData)] = $entityId;

        $customerId = 0;
        if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId($rowData[self::COLUMN_CUSTOMER_EMAIL], $this->orderStoreId) ?: 0;
        }

        $entityRowData = [
            'order_id' => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
            'order_item_id' => $orderItemId,
            'customer_id' => $customerId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate
        ];
    }

    /**
     * Prepare Data For Update
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];

        $orderId = $this->getOrderId($rowData) ?: $rowData['order_id'];
        if (!$orderId) {
            return false;
        }
        $orderItemId = $this->getOrderItemId($rowData);
        if (!$orderItemId) {
            return false;
        }

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }
        $this->downloadLinkIdsMapped[$this->getEntityId($rowData)] = $entityId;

        $customerId = 0;
        if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId($rowData[self::COLUMN_CUSTOMER_EMAIL], $this->orderStoreId) ?: 0;
        }

        $entityRowData = [
            'order_id' => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
            'order_item_id' => $orderItemId,
            'customer_id' => $customerId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate
        ];
    }

    /**
     * Retrieve Download Link Purchased Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $downloadLinkIdsMapped = $this->getDownloadLinkIdsMapped();
        if (empty($downloadLinkIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from($this->getMainTable(), self::COLUMN_ENTITY_ID)
                ->where(self::COLUMN_ENTITY_ID.' = :'.self::COLUMN_ENTITY_ID);

            $bind = [
                ':'.self::COLUMN_ENTITY_ID => $rowData[self::COLUMN_ENTITY_ID],
            ];

            $downloadLinkIdsMapped[$rowData[static::COLUMN_ENTITY_ID]] = $this->connection->fetchOne($select, $bind);
            $this->downloadLinkIdsMapped[$rowData[static::COLUMN_ENTITY_ID]] = $this->connection->fetchOne(
                $select,
                $bind
            ) ?: false;
        }

        return $downloadLinkIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
    }

    /**
     * Validate Row Data For Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function validateRowForUpdate(array $rowData, $rowNumber)
    {
        $this->validateEntityId($rowData, $rowNumber);

        if (!$this->checkExistEntityId($rowData)) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
        }

        foreach ($this->requiredValueColumns as $column) {
            if (isset($rowData[$column]) && '' == $rowData[$column]) {
                $this->addRowError(static::ERROR_COLUMN_IS_EMPTY, $rowNumber, $this->prefixCode.':'.$column);
            }
        }
    }

    /**
     * Validate Row Data For Add Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function validateRowForAdd(array $rowData, $rowNumber)
    {
        foreach ($this->requiredValueColumns as $column) {
            if (isset($rowData[$column]) && '' == $rowData[$column]) {
                $this->addRowError(static::ERROR_COLUMN_IS_EMPTY, $rowNumber, $this->prefixCode.':'.$column);
            }
        }
    }

    /**
     * Validate Row Data For Delete Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function validateRowForDelete(array $rowData, $rowNumber)
    {
        if ($this->validateEntityId($rowData, $rowNumber)) {
            if (!$this->checkExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            }
        }
    }
}
