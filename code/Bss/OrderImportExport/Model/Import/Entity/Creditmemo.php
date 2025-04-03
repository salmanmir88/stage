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
 * Order Creditmemo Import
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Creditmemo extends AbstractEntity
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
    const ERROR_ENTITY_ID_IS_EMPTY = 'creditmemoEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateCreditmemoEntityId';
    const ERROR_DUPLICATE_INCREMENT_ID = 'duplicateCreditmemoIncrementId';
    const ERROR_INCREMENT_ID_IS_EMPTY = 'creditmemoIncrementIdIsEmpty';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'creditmemoEntityIdIsNotExist';
    const ERROR_INCREMENT_ID_IS_EXIST = 'creditmemoIncrementIdIsExist';
    const ERROR_INCREMENT_ID_IS_NOT_EXIST = 'creditmemoIncrementIdIsNotExist';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'creditmemoStoreIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Creditmemo entity_id is duplicated',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Creditmemo increment_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Creditmemo entity_id is empty',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Creditmemo increment_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Creditmemo entity_id is not exist',
        self::ERROR_INCREMENT_ID_IS_EXIST => 'Creditmemo increment_id is exist',
        self::ERROR_INCREMENT_ID_IS_NOT_EXIST => 'Creditmemo increment_id is not exist',
        self::ERROR_STORE_ID_IS_NOT_EXIST => 'Creditmemo store_id is not exist',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_creditmemo';

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
        'shipping_tax_amount',
        'discount_amount',
        'adjustment_negative',
        'shipping_amount',
        'adjustment',
        'subtotal',
        'grand_total',
        'adjustment_positive',
        'tax_amount',
        'discount_tax_compensation_amount',
        'shipping_incl_tax',
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
    protected $prefixCode = Constant::PREFIX_CREDITMEMO;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Creditmemo\Item
     */
    protected $creditmemoItem;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Creditmemo\Comment
     */
    protected $creditmemoComment;

    /**
     * Creditmemo constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping
     * @param \Bss\OrderImportExport\Helper\Sequence $sequenceHelper
     * @param Creditmemo\ItemFactory $creditmemoItemFactory
     * @param Creditmemo\CommentFactory $creditmemoCommentFactory
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
        \Bss\OrderImportExport\Model\Import\Entity\Creditmemo\ItemFactory $creditmemoItemFactory,
        \Bss\OrderImportExport\Model\Import\Entity\Creditmemo\CommentFactory $creditmemoCommentFactory
    ) {
        $this->creditmemoItem = $creditmemoItemFactory->create();
        $this->creditmemoComment = $creditmemoCommentFactory->create();
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
            $this->creditmemoItem,
            $this->creditmemoComment
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
                $creditmemoIds = $this->getCreditmemoIdsMapped();
                $orderIds = $this->getOrderIdsMapped();
                $orderItemIds = $this->getItemIdsMapped();
                $child->setOrderIdsMapped($orderIds);
                $child->setItemIdsMapped($orderItemIds);
                $child->setCreditmemoIdsMapped($creditmemoIds);
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

        $this->setCreditmemoIdsMapped([]);

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
            $this->resource->getTableName('sales_creditmemo_grid'),
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

        if ($this->checkExistIncrementId($rowData)) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EXIST, $rowNumber);
            return false;
        }

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;

        if ($this->currentCreditmemoIncrementId) {
            $this->creditmemoIdsMapped[$this->currentCreditmemoIncrementId] = $entityId;
        } else {
            $this->creditmemoIdsMapped[$this->getEntityId($rowData)] = $entityId;
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
            $this->addRowError(__("The creditmemo increment_id is exist on other one"), $rowNumber);
            return false;
        }
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;

        if ($this->currentCreditmemoIncrementId) {
            $this->creditmemoIdsMapped[$this->currentCreditmemoIncrementId] = $entityId;
        } else {
            $this->creditmemoIdsMapped[$this->getEntityId($rowData)] = $entityId;
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
     * Retrieve Creditmemo Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistIncrementId(array $rowData)
    {
        $creditmemoIdsMapped = $this->getCreditmemoIdsMapped();
        if (!empty($creditmemoIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])) {
            return $creditmemoIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]];
        }
        return false;
    }

    /**
     * Retrieve Creditmemo Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $creditmemoIdsMapped = $this->getCreditmemoIdsMappedByEntityId();
        if (!empty($creditmemoIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $creditmemoIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
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
