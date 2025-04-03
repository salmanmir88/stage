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

use Magento\ImportExport\Model\Import;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Class Item
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 */
class Item extends AbstractEntity
{

    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'item_id';

    /**
     * Item SKU Column
     */
    const COLUMN_ITEM_SKU = 'sku';

    /**
     * Item Product Type Column
     */
    const COLUMN_ITEM_PRODUCT_TYPE = 'product_type';

    /**
     * Quote Item Id Column
     *
     */
    const COLUMN_QUOTE_ITEM_ID = 'quote_item_id';

    /**
     * Quote Item Id Column
     *
     */
    const COLUMN_ORDER_ID = 'order_id';

    /**
     * Product Id Column
     *
     */
    const COLUMN_PRODUCT_ID = 'product_id';

    /**
     * Parent Item Id Column
     *
     */
    const COLUMN_PARENT_ITEM_ID = 'parent_item_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'orderItemIdIsEmpty';
    const ERROR_COLUMN_SKU_IS_EMPTY = 'orderItemSkuIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateOrderItemId';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'orderItemIdIsNotExist';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'orderItemStoreIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Order Item item_id is duplicated in the import file',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Order Item item_id is empty',
        self::ERROR_COLUMN_SKU_IS_EMPTY => 'Order Item sku is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Order Item item_id is not exist',
        self::ERROR_STORE_ID_IS_NOT_EXIST => 'Order Item store_id is not exist',
    ];

    /**
     * Order Item Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_order_item';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'item_id',
        'parent_item_id',
        'product_type',
        'weight',
        'is_virtual',
        'sku',
        'name',
        'applied_rule_ids',
        'qty_canceled',
        'qty_invoiced',
        'qty_ordered',
        'qty_shipped',
        'price',
        'original_price',
        'discount_percent',
        'discount_amount',
        'discount_invoiced',
        'row_total',
        'row_invoiced',
        'row_weight',
        'price_incl_tax',
        'row_total_incl_tax',
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'item_id',
        'product_type',
        'sku',
        'name',
        'qty_ordered',
        'price',
        'original_price'
    ];

    /**
     * All columns has base prefix on database
     *
     * @var array
     */
    protected $baseFields = [
        'cost',
        'price',
        'original_price',
        'tax_amount',
        'tax_invoiced',
        'discount_amount',
        'discount_invoiced',
        'amount_refunded',
        'row_total',
        'row_invoiced',
        'tax_before_discount',
        'tax_refunded',
        'discount_refunded',
        'weee_tax_applied_amount',
        'weee_tax_applied_row_amnt',
        'weee_tax_disposition',
        'weee_tax_row_disposition',
        'discount_tax_compensation_amount',
        'discount_tax_compensation_invoiced',
        'discount_tax_compensation_refunded'
    ];

    /**
     * All columns has incl_tax suffix on database
     *
     * @var array
     */
    protected $inclTaxFields = [
        'price',
        'base_price',
        'row_total',
        'base_row_total'
    ];

    /**
     * List columns which has multiple value
     *
     * @var array
     */
    protected $multipleValueColumns = [
        'applied_rule_ids'
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_ITEM;

    /**
     * @var bool
     */
    protected $hasDownloadableItem = false;

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

        if ($this->getMultipleValueColumns()) {
            $multiSeparator = $this->getMultipleValueSeparator();
            foreach ($this->getMultipleValueColumns() as $multiValueCol) {
                if (!empty($rowData[$multiValueCol])) {
                    $values = [];
                    foreach (explode($multiSeparator, $rowData[$multiValueCol]) as $subValue) {
                        $values[] = $subValue;
                    }
                    $rowData[$multiValueCol] = implode(',', $values);
                }
            }
        }

        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    /**
     * Delete entities for replacement.
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    public function deleteForReplacement()
    {
        $this->setParameters(
            array_merge(
                $this->getParameters(),
                ['behavior' => Import::BEHAVIOR_DELETE]
            )
        );
        $this->deleteAction();

        $this->setItemIdsMapped([]);

        return $this;
    }

    /**
     * Prepare Data To Add Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareDataToAdd(array $rowData, $rowNumber)
    {
        $entitiesToCreate = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        if (empty($rowData['updated_at'])) {
            $updateAt = $now;
        } else {
            $updateAt = (new \DateTime())->setTimestamp(strtotime($rowData['updated_at']));
        }

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;
        $this->itemIdsMapped[$rowData[static::COLUMN_ENTITY_ID]] = $entityId;
        $this->itemIdsMapped[$this->currentIncrementId . $rowData['sku']] = $entityId;

        $quoteItemId = null;
        $parentItemId = null;

        if (!empty($rowData[self::COLUMN_QUOTE_ITEM_ID])) {
            $quoteItemId = $rowData[self::COLUMN_QUOTE_ITEM_ID];
        }

        if (!empty($rowData[self::COLUMN_PARENT_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_PARENT_ITEM_ID]])) {
                $parentItemId = $this->itemIdsMapped[$rowData[self::COLUMN_PARENT_ITEM_ID]];
            }
        }
        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }
        $productId = !empty($rowData[self::COLUMN_PRODUCT_ID])
            ? $rowData[self::COLUMN_PRODUCT_ID]
            : ($this->getProductIdBySku($rowData[self::COLUMN_ITEM_SKU]) ?: null);

        if (empty($rowData['no_discount'])) {
            $rowData['no_discount'] = 0;
        }

        if (empty($rowData['row_total'])) {
            $rowData['row_total'] = 0;
            $rowData['base_row_total'] = 0;
        }

        if (empty($rowData['row_invoiced'])) {
            $rowData['row_invoiced'] = 0;
            $rowData['base_row_invoiced'] = 0;
        }

        if (empty($rowData['free_shipping'])) {
            $rowData['free_shipping'] = 0;
        }

        if (empty($rowData['qty_returned'])) {
            $rowData['qty_returned'] = 0;
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);
        $entityRowData = [
            self::COLUMN_QUOTE_ITEM_ID => $quoteItemId,
            self::COLUMN_ORDER_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PARENT_ITEM_ID => $parentItemId,
            self::COLUMN_PRODUCT_ID => $productId,
            self::COLUMN_ITEM_SKU => $rowData[self::COLUMN_ITEM_SKU],
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $updateAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        if ($entityRowData[self::COLUMN_ITEM_PRODUCT_TYPE] == "downloadable") {
            $this->hasDownloadableItem = true;
        }

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate
        ];
    }

    /**
     * @return bool
     */
    public function hasDownloadableItem()
    {
        return $this->hasDownloadableItem;
    }

    /**
     * Prepare Data To Update Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }
        $this->itemIdsMapped[$rowData[static::COLUMN_ENTITY_ID]] = $entityId;

        $quoteItemId = null;
        $parentItemId = null;

        if (!empty($rowData[self::COLUMN_QUOTE_ITEM_ID])) {
            $quoteItemId = $rowData[self::COLUMN_QUOTE_ITEM_ID];
        }

        if (!empty($rowData[self::COLUMN_PARENT_ITEM_ID])) {
            if (isset($this->itemIdsMapped[$rowData[self::COLUMN_PARENT_ITEM_ID]])) {
                $parentItemId = $this->itemIdsMapped[$rowData[self::COLUMN_PARENT_ITEM_ID]];
            }
        }
        $orderId = $this->getOrderId($rowData) ?: $rowData[self::COLUMN_ORDER_ID];
        if (!$orderId) {
            return false;
        }

        $productId = !empty($rowData[self::COLUMN_PRODUCT_ID])
            ? $rowData[self::COLUMN_PRODUCT_ID]
            : ($this->getProductIdBySku($rowData[self::COLUMN_ITEM_SKU]) ?: null);

        if (empty($rowData['no_discount'])) {
            $rowData['no_discount'] = 0;
        }

        if (empty($rowData['row_total'])) {
            $rowData['row_total'] = 0;
            $rowData['base_row_total'] = 0;
        }

        if (empty($rowData['row_invoiced'])) {
            $rowData['row_invoiced'] = 0;
            $rowData['base_row_invoiced'] = 0;
        }

        if (empty($rowData['free_shipping'])) {
            $rowData['free_shipping'] = 0;
        }

        if (empty($rowData['qty_returned'])) {
            $rowData['qty_returned'] = 0;
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $entityRowData = [
            self::COLUMN_QUOTE_ITEM_ID => $quoteItemId,
            self::COLUMN_ORDER_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PARENT_ITEM_ID => $parentItemId,
            self::COLUMN_PRODUCT_ID => $productId,
            self::COLUMN_ITEM_SKU => $rowData[self::COLUMN_ITEM_SKU],
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
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

        $itemIdsMapped = $this->getItemIdsMapped();
        if (!empty($itemIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $itemIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
        }
        return false;
    }

    /**
     * Validate Row Data For Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    protected function validateRowForUpdate(array $rowData, $rowNumber)
    {
        $this->validateEntityId($rowData, $rowNumber);

        if (!empty($rowData[self::COLUMN_STORE_ID]) &&
            empty($this->getExistStores()[$rowData[self::COLUMN_STORE_ID]])
        ) {
            $this->addRowError(static::ERROR_STORE_ID_IS_NOT_EXIST, $rowNumber);
        }

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
     * @throws \Zend_Db_Statement_Exception
     */
    protected function validateRowForAdd(array $rowData, $rowNumber)
    {
        if ($this->validateEntityId($rowData, $rowNumber)) {
            $entityId = $rowData[self::COLUMN_ENTITY_ID];
            if (isset($this->newEntities[$entityId])) {
                $this->addRowError(self::ERROR_DUPLICATE_ENTITY_ID, $rowNumber);
            }

            if (!empty($rowData[self::COLUMN_STORE_ID]) &&
                empty($this->getExistStores()[$rowData[self::COLUMN_STORE_ID]])
            ) {
                $this->addRowError(static::ERROR_STORE_ID_IS_NOT_EXIST, $rowNumber);
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
