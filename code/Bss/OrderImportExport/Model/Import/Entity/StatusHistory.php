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

use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Order Status History Import
 */
class StatusHistory extends AbstractEntity
{
    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Order Id Column
     *
     */
    const COLUMN_ORDER_ID = 'parent_id';

    /**
     * Comment Column
     *
     */
    const COLUMN_COMMENT = 'comment';

    /**
     * Status Column
     *
     */
    const COLUMN_STATUS = 'status';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'statusHistoryEntityIdIsEmpty';
    const ERROR_ORDER_ID_IS_EMPTY = 'statusHistoryOrderIdIsEmpty';
    const ERROR_COMMENT_IS_EMPTY = 'statusHistoryCommentIsEmpty';
    const ERROR_STATUS_IS_EMPTY = 'statusHistoryStatusIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateStatusHistoryEntityId';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'statusHistoryEntityIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Status History entity_id is duplicated',
        self::ERROR_ORDER_ID_IS_EMPTY => 'Status History parent_id is empty',
        self::ERROR_COMMENT_IS_EMPTY => 'Status History comment is empty',
        self::ERROR_STATUS_IS_EMPTY => 'Status History status is empty',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Status History entity_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Status History entity_id is not exist',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_order_status_history';

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_STATUS_HISTORY;

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'comment',
        'entity_name',
        'status'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'comment',
        'entity_name',
        'status'
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
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
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

        $entityId = $this->getNextEntityId();

        if (empty($rowData['is_visible_on_front'])) {
            $rowData['is_visible_on_front'] = 0;
        }

        $entityRowData = [
            self::COLUMN_ORDER_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
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
     * Prepare Data To Update Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     * @throws \Exception
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

        $orderId = $this->getOrderId($rowData) ?: $rowData[self::COLUMN_ORDER_ID];
        if (!$orderId) {
            return false;
        }

        $entityId = $this->checkExistEntityId($rowData);
        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }

        if (empty($rowData['is_visible_on_front'])) {
            $rowData['is_visible_on_front'] = 0;
        }

        $entityRowData = [
            self::COLUMN_ORDER_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId,
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
     * Retrieve Entity Id
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData['entity_id'])) {
            return false;
        }

        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->connection->select();
        $select->from($this->getMainTable(), 'entity_id')
            ->where('entity_id = :entity_id');

        $bind = [
            ':entity_id' => $rowData['entity_id']
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
