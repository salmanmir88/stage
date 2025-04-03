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
 * Class Payment
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Payment extends AbstractEntity
{
    /**
     * Entity Id Column Name
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_PAYMENT;

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'paymentEntityIdIsEmpty';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'paymentEntityIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Payment entity_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Payment entity_id is not exist',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_order_payment';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'entity_id',
        'method',
        'amount_ordered',
        'shipping_amount',
        'additional_information'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'entity_id',
        'method',
        'amount_ordered',
        'shipping_amount'
    ];

    /**
     * All columns has base prefix on database
     *
     * @var array
     */
    protected $baseFields = [
        'amount_ordered',
        'shipping_amount',
        'shipping_captured',
        'amount_paid',
        'amount_authorized',
        'shipping_refunded',
        'amount_refunded',
        'amount_canceled'
    ];

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Payment\Transaction
     */
    protected $transaction;

    /**
     * @var \Magento\Framework\Serialize\SerializerInterface\Json
     */
    protected $serializerJson;

    /**
     * Payment constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping
     * @param \Bss\OrderImportExport\Helper\Sequence $sequenceHelper
     * @param Payment\TransactionFactory $transactionFactory
     * @param \Magento\Framework\Serialize\Serializer\Json $serializer
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
        \Bss\OrderImportExport\Model\Import\Entity\Payment\TransactionFactory $transactionFactory,
        \Magento\Framework\Serialize\Serializer\Json $serializerJson
    ) {
        $this->transaction = $transactionFactory->create();
        $this->serializerJson = $serializerJson;
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
            $this->transaction
        ];
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
     * Import Data
     *
     * @return bool
     */
    public function importData()
    {
        parent::importData();

        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $paymentIds = $this->getPaymentIdsMapped();
                $orderIds = $this->getOrderIdsMapped();
                $child->setOrderIdsMapped($orderIds);
                $child->setPaymentIdsMapped($paymentIds);
                $child->importData();
            }
        }

        return true;
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

        $this->setPaymentIdsMapped([]);

        return $this;
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

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;
        $this->paymentIdsMapped[$this->getEntityId($rowData)] = $entityId;

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }

        $rowData = $this->convertToBaseFields($rowData);

        if (empty($rowData['additional_information'])) {
            $rowData['additional_information'] = $this->serializerJson->serialize(['method_title' => $rowData['method']]);
        }

        $entityRowData = [
            'parent_id' => $orderId,
            'entity_id' => $entityId
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
        $this->paymentIdsMapped[$this->getEntityId($rowData)] = $entityId;

        $orderId = $this->getOrderId($rowData) ?: $rowData['parent_id'];
        if (!$orderId) {
            return false;
        }

        $rowData = $this->convertToBaseFields($rowData);

        if (empty($rowData['additional_information'])) {
            $rowData['additional_information'] = $this->serializerJson->serialize(['method_title' => $rowData['method']]);
        }

        $entityRowData = [
            'parent_id' => $orderId,
            'entity_id' => $entityId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate
        ];
    }

    /**
     * Retrieve Payment Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $paymentIdsMapped = $this->getPaymentIdsMapped();
        if (!empty($paymentIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $paymentIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
        }
        return false;
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
        $this->validateEntityId($rowData, $rowNumber);

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
