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

/**
 * Class Order
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Order extends AbstractEntity
{
    /**
     * Order Table
     *
     * @var string
     */
    protected $mainTable = 'sales_order';

    /**
     * Customer Email Column
     *
     */
    const COLUMN_CUSTOMER_EMAIL = 'customer_email';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'increment_id',
        'state',
        'status',
        'shipping_description',
        'is_virtual',
        'discount_invoiced',
        'grand_total',
        'shipping_amount',
        'shipping_invoiced',
        'subtotal',
        'subtotal_invoiced',
        'total_invoiced',
        'total_paid',
        'total_qty_ordered',
        'total_due',
        'customer_email',
        'order_currency_code',
        'shipping_method',
        'total_item_count',
        'store_id',
        'status_label',
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'increment_id',
        'state',
        'status',
        'is_virtual',
        'grand_total',
        'shipping_amount',
        'subtotal',
        'total_qty_ordered',
        'total_due',
        'customer_email',
        'order_currency_code',
        'total_item_count',
        'store_id',
        'status_label',
    ];

    /**
     * All columns has base prefix on database
     *
     * @var array
     */
    protected $baseFields = [
        'discount_amount',
        'discount_canceled',
        'discount_invoiced',
        'discount_refunded',
        'grand_total',
        'shipping_amount',
        'shipping_canceled',
        'shipping_invoiced',
        'shipping_refunded',
        'shipping_tax_amount',
        'shipping_tax_refunded',
        'subtotal',
        'subtotal_canceled',
        'subtotal_invoiced',
        'subtotal_refunded',
        'tax_amount',
        'tax_canceled',
        'tax_invoiced',
        'tax_refunded',
        'total_canceled',
        'total_invoiced',
        'total_invoiced_cost',
        'total_offline_refunded',
        'total_online_refunded',
        'total_paid',
        'total_refunded',
        'adjustment_negative',
        'adjustment_positive',
        'shipping_discount_amount',
        'subtotal_incl_tax',
        'total_due',
        'discount_tax_compensation_amount',
        'discount_tax_compensation_invoiced',
        'discount_tax_compensation_refunded',
        'shipping_incl_tax',
    ];

    /**
     * All columns has incl_tax suffix on database
     *
     * @var array
     */
    protected $inclTaxFields = [
        'subtotal',
        'base_subtotal'
    ];

    /**
     * Custom Csv Column For Entity
     *
     * @var array
     */
    protected $customColumns = [
        'status_label'
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
     * Validation Error Code
     *
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'orderEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateOrderEntityId';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'orderEntityIdIsNotExist';
    const ERROR_INCREMENT_ID_IS_EXIST = 'orderIncrementIdIsExist';
    const ERROR_INCREMENT_ID_IS_NOT_EXIST = 'orderIncrementIdIsNotExist';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'orderStoreIdIsNotExist';
    const ERROR_STATE_IS_NOT_EXIST = 'orderStateIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Order entity_id is duplicated in the import file',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Order increment_id is duplicated in the import file',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Order entity_id is empty',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Order increment_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Order entity_id is not exist',
        self::ERROR_INCREMENT_ID_IS_EXIST => 'Order increment_id is exist',
        self::ERROR_INCREMENT_ID_IS_NOT_EXIST => 'Order increment_id is not exist',
        self::ERROR_STORE_ID_IS_NOT_EXIST => 'Order store_id is not exist',
        self::ERROR_STATE_IS_NOT_EXIST => 'Order state code is not exist',
    ];

    /**
     * Order ids of a bunch
     *
     * @var array
     */
    protected $bunchOrderIds = [];

    /**
     * Retrieve Data For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Zend_Db_Statement_Exception
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

        if (!empty($rowData['status'])) {
            $rowData['status'] = strtolower($rowData['status']);
        }

        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    /**
     * Retrieve Extracted Field
     *
     * @param array $rowData
     * @param string $prefix
     * @return array|bool
     */
    protected function extractFields($rowData, $prefix)
    {
        $data = [];
        foreach ($rowData as $field => $value) {
            if ($field == "tax:percent") {
                $rowData['tax_percent'] = $value;
            }

            if ($prefix && strpos($field, ':') !== false) {
                list($fieldPrefix, $field) = explode(':', $field);
                if ($fieldPrefix == $prefix) {
                    $data[$field] = $value;
                }
            } elseif (!$this->prefixCode && strpos($field, ':') === false) {
                $data[$field] = $value;
            }
        }
        return $data;
    }

    /**
     * Retrieve Entity Id By Increment ID on Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistIncrementId(array $rowData)
    {
        $orderIdsMapped = $this->getOrderIdsMapped();
        if (!empty($orderIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])) {
            return $orderIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]];
        }
        return false;
    }

    /**
     * Retrieve Entity Id on Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $orderIdsMapped = $this->getOrderIdsMappedByEntityId();
        if (!empty($orderIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $orderIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
        }
        return false;
    }

    /**
     * Delete entities for replacement.
     *
     * @return $this
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

        $this->setOrderIdsMapped([]);

        return $this;
    }

    /**
     * Delete List Of Entities
     *
     * @param array $idsToDelete Entities Id List
     * @return $this
     */
    protected function deleteEntities(array $idsToDelete)
    {
        parent::deleteEntities($idsToDelete);
        $this->removeTax($idsToDelete);
        $this->removeDownloadLink($idsToDelete);
        $this->removeGrid($idsToDelete);
        $this->removeShipmentGrid($idsToDelete);
        $this->removeInvoiceGrid($idsToDelete);
        $this->removeCreditmemoGrid($idsToDelete);
        return $this;
    }

    /**
     * Remove order tax
     *
     * @param $orderIdsToDelete
     */
    protected function removeTax($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_order_tax'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove order download link purchased
     *
     * @param $orderIdsToDelete
     */
    protected function removeDownloadLink($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('downloadable_link_purchased'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on grid table
     *
     * @param $idsToDelete
     */
    protected function removeGrid($idsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_order_grid'),
            $this->connection->quoteInto(
                self::COLUMN_ENTITY_ID . ' IN (?)',
                $idsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on Shipment grid table
     *
     * @param $orderIdsToDelete
     */
    protected function removeShipmentGrid($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_shipment_grid'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on Invoice grid table
     *
     * @param $orderIdsToDelete
     */
    protected function removeInvoiceGrid($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_invoice_grid'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Remove deleted entities on Creditmemo grid table
     *
     * @param $orderIdsToDelete
     */
    protected function removeCreditmemoGrid($orderIdsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_creditmemo_grid'),
            $this->connection->quoteInto(
                'order_id IN (?)',
                $orderIdsToDelete
            )
        );
    }

    /**
     * Add Entities
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    protected function addAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $entitiesToCreate = [];
            $orderStatusToUpdate = [];
            $orderStatusLabelToUpdate = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->extractRowData($rowData);

                // validate entity data
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                $processedData = $this->prepareDataToAdd($rowData, $rowNumber);
                if (!$processedData) {
                    continue;
                }

                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                $orderStatusToUpdate = array_merge(
                    $orderStatusToUpdate,
                    $processedData[self::ORDER_STATUS_TO_UPDATE_KEY]
                );
                $orderStatusLabelToUpdate = array_merge(
                    $orderStatusLabelToUpdate,
                    $processedData[self::ORDER_STATUS_LABEL_TO_UPDATE_KEY]
                );
            }

            if ($entitiesToCreate) {
                $this->createEntities($entitiesToCreate);
                $this->updateOrderStatus($orderStatusToUpdate);
                $this->updateOrderStatusLabel($orderStatusLabelToUpdate);
            }
        }

        return $this;
    }

    /**
     * Update Entities
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    protected function updateAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $entitiesToUpdate = [];
            $orderStatusToUpdate = [];
            $orderStatusLabelToUpdate = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->extractRowData($rowData);

                // validate entity data
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                $processedData = $this->prepareDataToUpdate($rowData, $rowNumber);
                if (!$processedData) {
                    continue;
                }

                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
                $orderStatusToUpdate = array_merge(
                    $orderStatusToUpdate,
                    $processedData[self::ORDER_STATUS_TO_UPDATE_KEY]
                );
                $orderStatusLabelToUpdate = array_merge(
                    $orderStatusLabelToUpdate,
                    $processedData[self::ORDER_STATUS_LABEL_TO_UPDATE_KEY]
                );
            }

            if ($entitiesToUpdate) {
                $this->updateEntities($entitiesToUpdate);
                $this->updateOrderStatus($orderStatusToUpdate);
            }
        }

        return $this;
    }

    /**
     * Update Status For Order
     *
     * @param $statusData
     * @return $this
     */
    protected function updateOrderStatus($statusData)
    {
        if ($statusData) {
            $this->connection->insertOnDuplicate(
                $this->getOrderStatusTable(),
                $statusData,
                ['status', 'label']
            );
        }
        return $this;
    }

    /**
     * Update Status Label For Order
     *
     * @param $statusLabelData
     * @return $this
     */
    protected function updateOrderStatusLabel($statusLabelData)
    {
        if ($statusLabelData) {
            $this->connection->insertOnDuplicate(
                $this->getOrderStatusLabelTable(),
                $statusLabelData,
                ['status', 'store_id', 'label']
            );
        }
        return $this;
    }

    /**
     * Prepare Data To Add Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function prepareDataToAdd(array $rowData, $rowNumber)
    {
        $entitiesToCreate = [];
        $orderStatusToUpdate = [];
        $orderStatusLabelToUpdate = [];

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

        $customerId = null;
        $customerGroupId = 0;

        if ($this->checkExistIncrementId($rowData)) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EXIST, $rowNumber);
            return false;
        }

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        $this->mapOrderId($rowData[self::COLUMN_INCREMENT_ID], $entityId);

        if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId(
                $rowData[self::COLUMN_CUSTOMER_EMAIL],
                $rowData[self::COLUMN_STORE_ID] ?: 0
            ) ?: null;
            if (!empty($rowData['customer_group_id'])) {
                $customerGroupId = $rowData['customer_group_id'];
            }
        }

        if (empty($this->getExistStatus()[$rowData['status']])) {
            $orderStatusToUpdate[] = [
                'status' => $rowData['status'],
                'label' => $rowData['status_label']
            ];
        } else {
            $orderStatusLabelToUpdate[] = [
                'status' => $rowData['status'],
                'store_id' => $rowData['store_id'],
                'label' => $rowData['status_label']
            ];
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $this->bunchOrderIds[] = $entityId;

        $isVirtual = empty($rowData['is_virtual']) ? 0 : 1;
        $entityRowData = [
            'is_virtual' => $isVirtual,
            'customer_id' => $customerId,
            'customer_group_id' => $customerGroupId,
            'customer_is_guest' => $customerId ? 0 : 1,
            self::COLUMN_ENTITY_ID => $entityId,
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $updateAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ORDER_STATUS_TO_UPDATE_KEY => $orderStatusToUpdate,
            self::ORDER_STATUS_LABEL_TO_UPDATE_KEY => $orderStatusLabelToUpdate
        ];
    }

    /**
     * @return array
     */
    public function getBunchOrderIds()
    {
        return $this->bunchOrderIds;
    }

    /**
     * @return void
     */
    public function resetBunchOrderIds()
    {
        $this->bunchOrderIds = [];
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
     * @throws \Zend_Db_Statement_Exception
     */
    protected function prepareDataToUpdate(array $rowData, $rowNumber)
    {
        $entitiesToUpdate = [];
        $orderStatusToUpdate = [];
        $orderStatusLabelToUpdate = [];

        // entity table data
        $now = new \DateTime();
        if (empty($rowData['created_at'])) {
            $createdAt = $now;
        } else {
            $createdAt = (new \DateTime())->setTimestamp(strtotime($rowData['created_at']));
        }

        $customerId = null;
        $customerGroupId = 0;

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }

        if ($this->checkExistIncrementIdOnOtherOne($rowData)) {
            $this->addRowError(__("The order increment_id is exist on other one"), $rowNumber);
            return false;
        }

        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        $this->mapOrderId($rowData[self::COLUMN_INCREMENT_ID], $entityId);

        if (!empty($rowData[self::COLUMN_CUSTOMER_EMAIL])) {
            $customerId = $this->getCustomerId(
                $rowData[self::COLUMN_CUSTOMER_EMAIL],
                $rowData[self::COLUMN_STORE_ID] ?: 0
            ) ?: null;
            if ($customerId) {
                $customerGroupId = $this->getCustomerGroupId($rowData[self::COLUMN_CUSTOMER_EMAIL]);
            }
        }

        if (empty($this->getExistStatus()[$rowData['status']])) {
            $orderStatusToUpdate[] = [
                'status' => $rowData['status'],
                'label' => $rowData['status_label']
            ];
        } else {
            $orderStatusLabelToUpdate[] = [
                'status' => $rowData['status'],
                'store_id' => $rowData['store_id'],
                'label' => $rowData['status_label']
            ];
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $this->bunchOrderIds[] = $entityId;

        $isVirtual = empty($rowData['is_virtual']) ? 0 : 1;
        $entityRowData = [
            'is_virtual' => $isVirtual,
            'customer_id' => $customerId,
            'customer_group_id' => $customerGroupId,
            'customer_is_guest' => $customerId ? 0 : 1,
            self::COLUMN_ENTITY_ID => $entityId,
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate,
            self::ORDER_STATUS_TO_UPDATE_KEY => $orderStatusToUpdate,
            self::ORDER_STATUS_LABEL_TO_UPDATE_KEY => $orderStatusLabelToUpdate
        ];
    }

    /**
     * Add order id to map array
     *
     * @param $incrementId
     * @param $entityId
     */
    public function mapOrderId($incrementId, $entityId)
    {
        $this->orderIdsMapped[$incrementId] = $entityId;
    }

    /**
     * Add base rate to map array
     *
     * @param $incrementId
     * @param $baseRate
     */
    public function mapBaseRate($incrementId, $baseRate)
    {
        $this->baseRatesMapped[$incrementId] = $baseRate;
    }

    /**
     * Add tax rate to map array
     *
     * @param $incrementId
     * @param $taxRate
     */
    public function mapTaxRate($incrementId, $taxRate)
    {
        $this->taxRatesMapped[$incrementId] = $taxRate;
    }

    /**
     * Prepare Base Fields
     *
     * @param $rowData
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function convertToBaseFields($rowData)
    {
        $baseToOrderRate = 1;
        if (empty($rowData['base_to_order_rate'])) {
            $rowData['base_to_order_rate'] = $baseToOrderRate;
        } else {
            $baseToOrderRate = $rowData['base_to_order_rate'];
        }

        $this->mapBaseRate($rowData[self::COLUMN_INCREMENT_ID], $baseToOrderRate);

        if ($baseToOrderRate != 0) {
            foreach ($this->baseFields as $field) {
                if (!empty($rowData[$field]) && empty($rowData['base_' . $field])) {
                    $rowData['base_' . $field] = $rowData[$field] / $baseToOrderRate;
                }
            }

            if (isset($rowData['shipping_discount_tax_compensation_amount']) &&
                !isset($rowData['base_shipping_discount_tax_compensation_amnt'])
            ) {
                $rowData['base_shipping_discount_tax_compensation_amnt'] =
                    $rowData['shipping_discount_tax_compensation_amount'] / $baseToOrderRate;
            } elseif (!isset($rowData['shipping_discount_tax_compensation_amount']) &&
                isset($rowData['base_shipping_discount_tax_compensation_amnt'])
            ) {
                $rowData['shipping_discount_tax_compensation_amount'] =
                    $rowData['base_shipping_discount_tax_compensation_amnt'] * $baseToOrderRate;
            } elseif (!isset($rowData['shipping_discount_tax_compensation_amount']) &&
                !isset($rowData['base_shipping_discount_tax_compensation_amnt'])
            ) {
                $rowData['base_shipping_discount_tax_compensation_amnt'] =
                $rowData['shipping_discount_tax_compensation_amount'] = 0;
            }
        }

        if ($baseToOrderRate == 1) {
            // extract currency code
            foreach (['global_currency_code', 'base_currency_code', 'store_currency_code'] as $field) {
                if (!isset($rowData[$field]) && isset($rowData['order_currency_code'])) {
                    $rowData[$field] = $rowData['order_currency_code'];
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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function convertToInclTaxFields($rowData)
    {
        $taxRate = 0;
        if (!empty($rowData['tax_amount']) && !empty($rowData['subtotal']) && $rowData['subtotal'] != 0) {
            $taxRate = $rowData['tax_amount'] / $rowData['subtotal'];
        }
        if (!empty($rowData['tax_percent'])) {
            $taxRate = $rowData['tax_percent'] / 100;
        }
        $this->mapBaseRate($rowData[self::COLUMN_INCREMENT_ID], $taxRate);

        foreach ($this->inclTaxFields as $field) {
            if (!empty($rowData[$field]) && empty($rowData[$field . '_incl_tax'])) {
                $rowData[$field . '_incl_tax'] = $rowData[$field] * (1 + $taxRate);
            }
        }

        if (!empty($rowData['shipping_amount']) && empty($rowData['shipping_incl_tax'])) {
            $rowData['shipping_incl_tax'] = $rowData['shipping_amount'] * (1 + $taxRate);
        }
        if (!empty($rowData['base_shipping_amount']) && empty($rowData['base_shipping_incl_tax'])) {
            $rowData['base_shipping_incl_tax'] = $rowData['base_shipping_amount'] * (1 + $taxRate);
        }

        return $rowData;
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

    /**
     * Validate Row Data For Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateRowForUpdate(array $rowData, $rowNumber)
    {
        if ($this->validateEntityId($rowData, $rowNumber) && $this->validateIncrementId($rowData, $rowNumber)) {
            $incrementId = $rowData[self::COLUMN_INCREMENT_ID];
            if (isset($this->newEntities[$incrementId])) {
                $this->addRowError(self::ERROR_DUPLICATE_INCREMENT_ID, $rowNumber);
            } else {
                $this->newEntities[$incrementId] = true;
            }

            if (!empty($rowData[self::COLUMN_STORE_ID]) &&
                empty($this->getExistStores()[$rowData[self::COLUMN_STORE_ID]])
            ) {
                $this->addRowError(static::ERROR_STORE_ID_IS_NOT_EXIST, $rowNumber);
            }

            if (!empty($rowData[self::COLUMN_STATE]) &&
                !in_array($rowData[self::COLUMN_STATE], $this->getExistStates())
            ) {
                $this->addRowError(static::ERROR_STATE_IS_NOT_EXIST, $rowNumber);
            }

            if (!$this->checkExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            }

            foreach ($this->requiredValueColumns as $column) {
                if (isset($rowData[$column]) && '' == $rowData[$column]) {
                    $this->addRowError(static::ERROR_COLUMN_IS_EMPTY, $rowNumber, $column);
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
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function validateRowForAdd(array $rowData, $rowNumber)
    {
        if ($this->validateIncrementId($rowData, $rowNumber)) {
            $incrementId = $rowData[self::COLUMN_INCREMENT_ID];
            if (isset($this->newEntities[$incrementId])) {
                $this->addRowError(self::ERROR_DUPLICATE_INCREMENT_ID, $rowNumber);
            } else {
                $this->newEntities[$incrementId] = true;
            }

            if (!empty($rowData[self::COLUMN_STORE_ID]) &&
                empty($this->getExistStores()[$rowData[self::COLUMN_STORE_ID]])
            ) {
                $this->addRowError(static::ERROR_STORE_ID_IS_NOT_EXIST, $rowNumber);
            }

            if (!empty($rowData[self::COLUMN_STATE]) &&
                !in_array($rowData[self::COLUMN_STATE], $this->getExistStates())
            ) {
                $this->addRowError(static::ERROR_STATE_IS_NOT_EXIST, $rowNumber);
            }

            if ($this->checkExistIncrementId($rowData)) {
                $this->addRowError(static::ERROR_INCREMENT_ID_IS_EXIST, $rowNumber);
            }

            foreach ($this->requiredValueColumns as $column) {
                if (isset($rowData[$column]) && '' == $rowData[$column]) {
                    $this->addRowError(static::ERROR_COLUMN_IS_EMPTY, $rowNumber, $column);
                }
            }

            $this->validateShippingMethod($rowData, $rowNumber);
        }
    }

    /**
     * * Validate Shipping Method Data
     *
     * @param array $rowData
     * @param $rowNumber
     */
    protected function validateShippingMethod(array $rowData, $rowNumber)
    {
        $isVirtual = empty($rowData['is_virtual']) ? 0 : 1;
        if (!$isVirtual && empty($rowData['shipping_method'])) {
            $this->addRowError(
                static::ERROR_CODE_ATTRIBUTE_NOT_VALID,
                $rowNumber,
                'shipping_method'
            );
        }

        if (!$isVirtual &&
            !empty($rowData['shipping_method']) &&
            !preg_match('/[a-z]_[a-z]/', $rowData['shipping_method'])
        ) {
            $this->addRowError(
                __('The format of shipping_method must be CarrierCode_MethodCode'),
                $rowNumber
            );
        }
    }
}
