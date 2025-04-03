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
namespace Bss\OrderImportExport\Model\Import\Entity\Creditmemo;

use Bss\OrderImportExport\Model\Import\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Order Creditmemo Comment Import
 */
class Comment extends AbstractEntity
{
    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Shipment Id Column
     *
     */
    const COLUMN_CREDITMEMO_ID = 'parent_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'creditmemoCommentIdIsEmpty';
    const ERROR_CREDITMEMO_ID_IS_EMPTY = 'creditmemoCommentParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateCreditmemoCommentId';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Creditmemo Comment entity_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Creditmemo Comment entity_id is empty',
        self::ERROR_CREDITMEMO_ID_IS_EMPTY => 'Creditmemo Comment parent_id is empty',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_creditmemo_comment';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'comment'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'comment'
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_CREDITMEMO_COMMENT;

    /**
     * Retrieve Data For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
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

        $creditmemoId = $this->getCreditmemoId($rowData);
        if (!$creditmemoId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        if (empty($rowData['is_visible_on_front'])) {
            $rowData['is_visible_on_front'] = 0;
        }

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $creditmemoId,
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

        $creditmemoId = $this->getCreditmemoId($rowData) ?: $rowData[static::COLUMN_CREDITMEMO_ID];
        if (!$creditmemoId) {
            return false;
        }

        if (empty($rowData['is_visible_on_front'])) {
            $rowData['is_visible_on_front'] = 0;
        }

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $creditmemoId,
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
            if (empty($rowData[self::COLUMN_SHIPMENT_ID])) {
                $this->addRowError(self::ERROR_CREDITMEMO_ID_IS_EMPTY, $rowNumber);
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
