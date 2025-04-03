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
namespace Bss\OrderImportExport\Model\Import\Entity\Tax;

use Bss\OrderImportExport\Model\Import\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Order Shipment Item Import
 */
class Item extends AbstractEntity
{
    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'tax_item_id';

    /**
     * Tax Id Column
     *
     */
    const COLUMN_TAX_ID = 'tax_id';

    /**
     * Order Item Id Column
     *
     */
    const COLUMN_ORDER_ITEM_ID = 'item_id';

    /**
     * Associated Order Item Id Column
     *
     */
    const COLUMN_ASSOCIATED_ITEM_ID = 'associated_item_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'taxItemIdIsEmpty';
    const ERROR_TAX_ID_IS_EMPTY = 'taxItemParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateTaxItemId';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Tax Item tax_item_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Tax Item tax_item_id is empty',
        self::ERROR_TAX_ID_IS_EMPTY => 'Tax Item tax_id is empty'
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_order_tax_item';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'tax_id',
        'item_id',
        'tax_percent',
        'amount',
        'taxable_item_type'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'tax_id',
        'tax_percent',
        'amount',
        'taxable_item_type'
    ];

    /**
     * All columns has base prefix on database
     *
     * @var array
     */
    protected $baseFields = [
        'amount'
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_TAX_ITEM;

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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareDataToAdd(array $rowData, $rowNumber)
    {
        $entitiesToCreate = [];

        $taxId = $this->getTaxId($rowData);
        if (!$taxId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        $associatedItemId = null;
        if (!empty($rowData[self::COLUMN_ASSOCIATED_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_ASSOCIATED_ITEM_ID]])) {
                $associatedItemId = $this->itemIdsMapped[$rowData[self::COLUMN_ASSOCIATED_ITEM_ID]];
            }
        }

        $itemId = null;
        if (!empty($rowData[self::COLUMN_ORDER_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]])) {
                $itemId = $this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]];
            }
        }

        if (empty($rowData['amount'])) {
            $rowData['amount'] = 0;
        }

        if (empty($rowData['base_amount'])) {
            $rowData['base_amount'] = 0;
        }

        if (empty($rowData['real_amount'])) {
            $rowData['real_amount'] = 0;
        }

        if (empty($rowData['real_base_amount'])) {
            $rowData['real_base_amount'] = 0;
        }

        $entityRowData = [
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_TAX_ID => $taxId,
            self::COLUMN_ORDER_ITEM_ID => $itemId,
            self::COLUMN_ASSOCIATED_ITEM_ID => $associatedItemId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate
        ];
    }

    /**
     * Prepare Data To Update Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }

        $taxId = $this->getTaxId($rowData) ?: $rowData[self::COLUMN_TAX_ID];
        if (!$taxId) {
            return false;
        }

        $associatedItemId = null;
        if (!empty($rowData[self::COLUMN_ASSOCIATED_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_ASSOCIATED_ITEM_ID]])) {
                $associatedItemId = $this->itemIdsMapped[$rowData[self::COLUMN_ASSOCIATED_ITEM_ID]];
            }
        }

        $itemId = null;
        if (!empty($rowData[self::COLUMN_ORDER_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]])) {
                $itemId = $this->itemIdsMapped[$rowData[self::COLUMN_ORDER_ITEM_ID]];
            }
        }

        if (empty($rowData['amount'])) {
            $rowData['amount'] = 0;
        }

        if (empty($rowData['base_amount'])) {
            $rowData['base_amount'] = 0;
        }

        if (empty($rowData['real_amount'])) {
            $rowData['real_amount'] = 0;
        }

        if (empty($rowData['real_base_amount'])) {
            $rowData['real_base_amount'] = 0;
        }

        $entityRowData = [
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_TAX_ID => $taxId,
            self::COLUMN_ORDER_ITEM_ID => $itemId,
            self::COLUMN_ASSOCIATED_ITEM_ID => $associatedItemId
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
