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
namespace Bss\OrderImportExport\Model\Import\Entity\DownloadLink;

use Bss\OrderImportExport\Model\Import\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Class Item
 *
 * @package Bss\OrderImportExport\Model\Import\Entity\DownloadLink
 */
class Item extends AbstractEntity
{
    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'item_id';

    /**
     * Download Link Purchased Id Column
     *
     */
    const COLUMN_PARENT_ID = 'purchased_id';

    /**
     * Order Item Id Column
     *
     */
    const COLUMN_ORDER_ITEM_ID = 'order_item_id';

    /**
     * Link Url Column
     *
     */
    const COLUMN_LINK_URL = 'link_url';

    /**
     * Link File Column
     *
     */
    const COLUMN_LINK_FILE = 'link_file';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'downloadLinkItemIdIsEmpty';
    const ERROR_PURCHASED_ID_IS_EMPTY = 'downloadLinkItemParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateDownloadLinkItemId';
    const ERROR_LINK_IS_EMPTY = 'downloadLinkItemLinkIsEmpty';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Download Link Item item_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Download Link Item item_id is empty',
        self::ERROR_PURCHASED_ID_IS_EMPTY => 'Download Link Item purchased_id is empty',
        self::ERROR_LINK_IS_EMPTY => 'Download Link Item link_url and link_file is empty'
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'downloadable_link_purchased_item';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'item_id',
        'purchased_id',
        'order_item_id',
        'link_url',
        'link_file'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'purchased_id',
        'order_item_id'
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
    protected $prefixCode = Constant::PREFIX_ORDER_DOWNLOAD_LINK_ITEM;

    /**
     * Retrieve Data For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function extractRowData(array $rowData, $rowNumber = 0)
    {
        $rowData = parent::extractRowData($rowData);
        $rowData = $this->extractFields($rowData, $this->prefixCode);
        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    /**
     * Prepare Data To Add Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function prepareDataToAdd(array $rowData, $rowNumber)
    {
        $entitiesToCreate = [];

        $purchasedId = $this->getDownloadLinkId($rowData);
        if (!$purchasedId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        $orderItemId = null;
        if (!empty($rowData[self::COLUMN_ORDER_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]])) {
                $orderItemId = $this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]];
            }
        }

        $entityRowData = [
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PARENT_ID => $purchasedId,
            self::COLUMN_ORDER_ITEM_ID => $orderItemId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate
        ];
    }

    /**
     * @return bool
     */
    protected function getDownloadLinkId($rowData)
    {
        if (null !== $this->downloadLinkIdsMapped) {
            $purchasedId = $rowData[static::COLUMN_PARENT_ID];
            if (isset($this->downloadLinkIdsMapped[$purchasedId])) {
                return $this->downloadLinkIdsMapped[$purchasedId];
            }
        }
        return false;
    }

    /**
     * Prepare Data To Update Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }

        $purchasedId = $this->getDownloadLinkId($rowData) ?: $rowData[self::COLUMN_PARENT_ID];
        if (!$purchasedId) {
            return false;
        }

        $orderItemId = null;
        if (!empty($rowData[self::COLUMN_ORDER_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]])) {
                $orderItemId = $this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]];
            }
        }

        $entityRowData = [
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PARENT_ID => $purchasedId,
            self::COLUMN_ORDER_ITEM_ID => $orderItemId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate
        ];
    }

    /**
     * Retrieve Entity Id
     * Can optimize
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->connection->select();
        $select->from($this->getMainTable(), self::COLUMN_ENTITY_ID)
            ->where(self::COLUMN_ENTITY_ID.' = :'.self::COLUMN_ENTITY_ID);

        $bind = [
            ':'.self::COLUMN_ENTITY_ID => $rowData[self::COLUMN_ENTITY_ID],
        ];

        return $this->connection->fetchOne($select, $bind);
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
        if ($this->validateEntityId($rowData, $rowNumber)) {
            if (!$this->checkExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            }

            foreach ($this->requiredValueColumns as $column) {
                if (isset($rowData[$column]) && '' == $rowData[$column]) {
                    $this->addRowError(
                        static::ERROR_COLUMN_IS_EMPTY,
                        $rowNumber,
                        $this->prefixCode.':'.$column
                    );
                }
            }

            if (empty($rowData[self::COLUMN_LINK_URL]) && empty($rowData[self::COLUMN_LINK_FILE])) {
                $this->addRowError(static::ERROR_LINK_IS_EMPTY, $rowNumber);
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

        if (empty($rowData[self::COLUMN_LINK_URL]) && empty($rowData[self::COLUMN_LINK_FILE])) {
            $this->addRowError(static::ERROR_LINK_IS_EMPTY, $rowNumber);
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
