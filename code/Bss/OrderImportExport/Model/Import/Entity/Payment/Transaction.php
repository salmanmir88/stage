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
namespace Bss\OrderImportExport\Model\Import\Entity\Payment;

use Bss\OrderImportExport\Model\Import\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Order Payment Transaction Import
 */
class Transaction extends AbstractEntity
{
    /**
     * Entity Id Column Name
     *
     */
    const COLUMN_ENTITY_ID = 'transaction_id';

    /**
     * Payment Id Column Name
     *
     */
    const COLUMN_PAYMENT_ID = 'payment_id';

    /**
     * Transaction Parent Id Column Name
     *
     */
    const COLUMN_PARENT_ID = 'parent_id';

    /**
     * Order Id Column Name
     *
     */
    const COLUMN_ORDER_ID = 'order_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'paymentTransactionEntityIdIsEmpty';
    const ERROR_PAYMENT_ID_IS_EMPTY = 'paymentTransactionPaymentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicatePaymentTransactionId';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Payment Transaction transaction_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Payment Transaction transaction_id is empty',
        self::ERROR_PAYMENT_ID_IS_EMPTY => 'Payment Transaction payment_id is empty',
    ];

    /**
     * Transaction Ids Map
     *
     * @var array
     */
    protected $transactionIdsMapped;

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_payment_transaction';

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_PAYMENT_TRANSACTION;

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'payment_id'
    ];

    /**
     * Retrieve Transaction Ids Map
     *
     * @return array
     */
    public function getTransactionIdsMapped()
    {
        return $this->transactionIdsMapped ?: [];
    }

    /**
     * Set Transaction Ids Map
     *
     * @param array $transactionIds
     * @return $this
     */
    public function setTransactionIdsMapped(array $transactionIds)
    {
        $this->transactionIdsMapped = $transactionIds;

        return $this;
    }

    /**
     * Retrieve Transaction Parent Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getParentId(array $rowData)
    {
        if (null !== $this->transactionIdsMapped) {
            $parentId = $rowData[self::COLUMN_PARENT_ID];
            if (isset($this->transactionIdsMapped[$parentId])) {
                return $this->transactionIdsMapped[$parentId];
            }
        }
        return false;
    }

    /**
     * Retrieve Data For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function extractRowData(array $rowData, $rowNumber = 0)
    {
        $rowData = parent::extractRowData($rowData);
        $rowData = $this->extractFields($rowData, $this->prefixCode);
        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    /**
     * Prepare Data To Add
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

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }

        $paymentId = $this->getPaymentId($rowData);
        if (!$paymentId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        $this->transactionIdsMapped[$this->getEntityId($rowData)] = $entityId;

        if (empty($rowData['is_closed'])) {
            $rowData['is_closed'] = 1;
        }

        $entityRowData = [
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PAYMENT_ID => $paymentId,
            self::COLUMN_ORDER_ID => $orderId,
            self::COLUMN_PARENT_ID => empty($rowData[self::COLUMN_PARENT_ID]) ? null : $this->getParentId($rowData),
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate
        ];
    }

    /**
     * Prepare Data To Update
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

        $paymentId = $this->getPaymentId($rowData) ?: $rowData[self::COLUMN_PAYMENT_ID];
        if (!$paymentId) {
            return false;
        }

        $this->transactionIdsMapped[$this->getEntityId($rowData)] = $entityId;

        if (empty($rowData['is_closed'])) {
            $rowData['is_closed'] = 1;
        }

        $entityRowData = [
            self::COLUMN_ENTITY_ID => $entityId,
            self::COLUMN_PAYMENT_ID => $this->getPaymentId($rowData) ?: $rowData[self::COLUMN_PAYMENT_ID],
            self::COLUMN_ORDER_ID => $this->getOrderId($rowData) ?: $rowData[self::COLUMN_ORDER_ID],
            self::COLUMN_PARENT_ID => empty($rowData[self::COLUMN_PARENT_ID]) ? null : $this->getParentId($rowData),
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToUpdate[] = $entityRowData;
        return [
            self::ENTITIES_TO_UPDATE_KEY => $entitiesToUpdate
        ];
    }

    /**
     * Retrieve Transaction Id From Database
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
            if (empty($rowData[self::COLUMN_PAYMENT_ID])) {
                $this->addRowError(self::ERROR_PAYMENT_ID_IS_EMPTY, $rowNumber);
            }

            if (!$this->checkExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
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
        if (empty($rowData[self::COLUMN_PAYMENT_ID])) {
            $this->addRowError(self::ERROR_PAYMENT_ID_IS_EMPTY, $rowNumber);
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
