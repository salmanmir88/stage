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
namespace Bss\OrderImportExport\Model\Import\Entity\Invoice;

use Bss\OrderImportExport\Model\Import\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Class Item
 *
 * @package Bss\OrderImportExport\Model\Import\Entity\Invoice
 */
class Item extends AbstractEntity
{
    /**
     * Entity Id Column Name
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Invoice Id Column Name
     *
     */
    const COLUMN_INVOICE_ID = 'parent_id';

    /**
     * Order Item Id Column Name
     *
     */
    const COLUMN_ORDER_ITEM_ID = 'order_item_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'invoiceItemIdIsEmpty';
    const ERROR_INVOICE_ID_IS_EMPTY = 'invoiceItemParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateInvoiceItemId';
    const ERROR_ORDER_ITEM_ID_IS_EMPTY = 'invoiceItemOrderItemIdIsEmpty';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Invoice Item entity_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Invoice Item entity_id is empty',
        self::ERROR_INVOICE_ID_IS_EMPTY => 'Invoice Item parent_id is empty',
        self::ERROR_ORDER_ITEM_ID_IS_EMPTY => 'Invoice Item order_item_id is empty',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_invoice_item';

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_INVOICE_ITEM;

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'sku',
        'name',
        'price',
        'discount_amount',
        'row_total',
        'order_item_id',
        'qty'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'sku',
        'name',
        'price',
        //'row_total',
        'order_item_id',
        'qty'
    ];

    /**
     * @var array
     */
    protected $baseFields = [
        'cost',
        'price',
        'tax_amount',
        'discount_amount',
        'row_total',
        'row_total_incl_tax',
        'weee_tax_applied_amount',
        'weee_tax_disposition',
        'weee_tax_row_disposition',
        'discount_tax_compensation_amount',
    ];

    /**
     * @var array
     */
    protected $inclTaxFields = [
        'price',
        'base_price',
        'row_total',
        'base_row_total'
    ];

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

        $invoiceId = $this->getInvoiceId($rowData);
        if (!$invoiceId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $invoiceId,
            'order_item_id' => $this->findOrderItemId($rowData) ?: null
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
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }

        $invoiceId = $this->getInvoiceId($rowData) ?: $rowData[static::COLUMN_INVOICE_ID];
        if (!$invoiceId) {
            return false;
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $invoiceId,
            'order_item_id' => $this->getOrderItemId($rowData) ?: null
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate
        ];
    }

    /**
     * Prepare Base Fields
     *
     * @param $rowData
     * @return mixed
     */
    protected function convertToBaseFields($rowData)
    {
        $baseToOrderRate = $this->getBaseRate($rowData);

        if ($baseToOrderRate != 0) {
            foreach ($this->baseFields as $field) {
                if (!empty($rowData[$field]) && empty($rowData['base_' . $field])) {
                    $rowData['base_' . $field] = $rowData[$field] / $baseToOrderRate;
                }
            }
        }

        return $rowData;
    }

    /**
     * Prepare Incl Tax Fields
     *
     * @param $rowData
     * @return mixed
     */
    protected function convertToInclTaxFields($rowData)
    {
        $taxRate = $this->getTaxRate($rowData);

        foreach ($this->inclTaxFields as $field) {
            if (!empty($rowData[$field]) && empty($rowData[$field . '_incl_tax'])) {
                $rowData[$field . '_incl_tax'] = $rowData[$field] * (1 + $taxRate);
            }
        }

        return $rowData;
    }

    /**
     * Retrieve Item Id By Product Info
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
     * Retrieve Order Item Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function findOrderItemId(array $rowData)
    {
        $key = $this->currentIncrementId . $rowData['sku'];
        if (isset($this->itemIdsMapped[$key])) {
            return $this->itemIdsMapped[$key];
        }
        return parent::getOrderItemId($rowData);
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
            if (empty($rowData[self::COLUMN_INVOICE_ID])) {
                $this->addRowError(self::ERROR_INVOICE_ID_IS_EMPTY, $rowNumber);
            }

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
