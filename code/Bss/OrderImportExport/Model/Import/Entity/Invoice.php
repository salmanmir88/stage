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
use Magento\ImportExport\Model\Import;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Class Invoice
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Invoice extends AbstractEntity
{
    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    const COLUMN_ORDER_ID = 'order_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'invoiceEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateInvoiceEntityId';
    const ERROR_DUPLICATE_INCREMENT_ID = 'duplicateInvoiceIncrementId';
    const ERROR_INCREMENT_ID_IS_EMPTY = 'invoiceIncrementIdIsEmpty';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'invoiceEntityIdIsNotExist';
    const ERROR_INCREMENT_ID_IS_EXIST = 'invoiceIncrementIdIsExist';
    const ERROR_INCREMENT_ID_IS_NOT_EXIST = 'invoiceIncrementIdIsNotExist';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'invoiceStoreIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Invoice entity_id is duplicate',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Invoice increment_id is duplicate',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Invoice entity_id is empty',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Invoice increment_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Invoice entity_id is not exist',
        self::ERROR_INCREMENT_ID_IS_EXIST => 'Invoice increment_id is exist',
        self::ERROR_INCREMENT_ID_IS_NOT_EXIST => 'Invoice increment_id is not exist',
        self::ERROR_STORE_ID_IS_NOT_EXIST => 'Invoice store_id is not exist',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_invoice';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'increment_id',
        'store_id',
        'grand_total',
        'tax_amount',
        'shipping_tax_amount',
        'discount_amount',
        'shipping_amount',
        'subtotal'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'increment_id',
        'store_id',
        'grand_total',
        'subtotal'
    ];

    /**
     * @var array
     */
    protected $baseFields = [
        'grand_total',
        'tax_amount',
        'shipping_tax_amount',
        'discount_amount',
        'shipping_amount',
        'subtotal',
        'discount_tax_compensation_amount',
    ];

    /**
     * @var array
     */
    protected $inclTaxFields = [
        'base_subtotal',
        'subtotal',
        'shipping',
        'base_shipping',
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_INVOICE;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Invoice\Item
     */
    protected $invoiceItem;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Invoice\Comment
     */
    protected $invoiceComment;

    /**
     * Invoice constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping
     * @param \Bss\OrderImportExport\Helper\Sequence $sequenceHelper
     * @param Invoice\ItemFactory $invoiceItemFactory
     * @param Invoice\CommentFactory $invoiceCommentFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
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
        \Bss\OrderImportExport\Model\Import\Entity\Invoice\ItemFactory $invoiceItemFactory,
        \Bss\OrderImportExport\Model\Import\Entity\Invoice\CommentFactory $invoiceCommentFactory
    ) {
        $this->invoiceItem = $invoiceItemFactory->create();
        $this->invoiceComment = $invoiceCommentFactory->create();
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
            $this->invoiceItem,
            $this->invoiceComment
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
                $shipmentIds = $this->getInvoiceIdsMapped();
                $orderIds = $this->getOrderIdsMapped();
                $orderItemIds = $this->getItemIdsMapped();
                $child->setOrderIdsMapped($orderIds);
                $child->setItemIdsMapped($orderItemIds);
                $child->setInvoiceIdsMapped($shipmentIds);
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

        $this->setInvoiceIdsMapped([]);

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
        $this->removeGrid($idsToDelete);
        return $this;
    }

    /**
     * Remove deleted entities on grid table
     *
     * @param $idsToDelete
     */
    protected function removeGrid($idsToDelete)
    {
        $this->connection->delete(
            $this->resource->getTableName('sales_invoice_grid'),
            $this->connection->quoteInto(
                self::COLUMN_ENTITY_ID . ' IN (?)',
                $idsToDelete
            )
        );
    }

    /**
     * Prepare Data To Add Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Exception
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

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }

        if ($this->checkExistIncrementId($rowData)) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EXIST, $rowNumber);
            return false;
        }

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;
        if ($this->currentInvoiceIncrementId) {
            $this->invoiceIdsMapped[$this->currentInvoiceIncrementId] = $entityId;
        } else {
            $this->invoiceIdsMapped[$this->getEntityId($rowData)] = $entityId;
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $entityRowData = [
            'order_id' => $orderId,
            'entity_id' => $entityId,
            'shipping_address_id' => $this->getShippingAddressId($rowData),
            'billing_address_id' => $this->getBillingAddressId($rowData),
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $updateAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
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

        $orderId = $this->getOrderId($rowData) ?: $rowData[self::COLUMN_ORDER_ID];
        if (!$orderId) {
            return false;
        }

        if ($this->checkExistIncrementIdOnOtherOne($rowData)) {
            $this->addRowError(__("The invoice increment_id is exist on other one"), $rowNumber);
            return false;
        }
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;

        if ($this->currentInvoiceIncrementId) {
            $this->invoiceIdsMapped[$this->currentInvoiceIncrementId] = $entityId;
        } else {
            $this->invoiceIdsMapped[$this->getEntityId($rowData)] = $entityId;
        }

        $rowData = $this->convertToInclTaxFields($rowData);
        $rowData = $this->convertToBaseFields($rowData);

        $entityRowData = [
            'order_id' => $orderId,
            'entity_id' => $entityId,
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        if (!empty($rowData['billing_address_id']) && $this->getAddressId($rowData['billing_address_id'])) {
            $entityRowData['billing_address_id'] = $this->getAddressId($rowData['billing_address_id']);
        }

        if (!empty($rowData['shipping_address_id']) && $this->getAddressId($rowData['shipping_address_id'])) {
            $entityRowData['shipping_address_id'] = $this->getAddressId($rowData['shipping_address_id']);
        }

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
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
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

            if (isset($rowData['shipping_discount_tax_compensation_amount']) &&
                !isset($rowData['base_shipping_discount_tax_compensation_amnt'])
            ) {
                $rowData['base_shipping_discount_tax_compensation_amnt'] =
                    $rowData['shipping_discount_tax_compensation_amount'] / $baseToOrderRate;
            } else {
                $rowData['base_shipping_discount_tax_compensation_amnt'] =
                $rowData['shipping_discount_tax_compensation_amount'] = 0;
            }
        }

        if ($baseToOrderRate == 1) {
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
     * Retrieve Invoice Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistIncrementId(array $rowData)
    {
        $invoiceIdsMapped = $this->getInvoiceIdsMapped();
        if (!empty($invoiceIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])) {
            return $invoiceIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]];
        }
        return false;
    }

    /**
     * Retrieve Invoice Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $invoiceIdsMapped = $this->getInvoiceIdsMappedByEntityId();
        if (!empty($invoiceIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $invoiceIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
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
     * @throws \Zend_Db_Statement_Exception
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

            if ($this->checkExistIncrementId($rowData)) {
                $this->addRowError(static::ERROR_INCREMENT_ID_IS_EXIST, $rowNumber);
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
