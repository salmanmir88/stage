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
 * Order Invoice Comment Import
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
    const COLUMN_INVOICE_ID = 'parent_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'invoiceCommentIdIsEmpty';
    const ERROR_INVOICE_ID_IS_EMPTY = 'invoiceCommentParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateInvoiceCommentId';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Invoice Comment entity_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Invoice Comment entity_id is empty',
        self::ERROR_INVOICE_ID_IS_EMPTY => 'Invoice Comment parent_id is empty'
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_invoice_comment';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'comment',
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
    protected $prefixCode = Constant::PREFIX_INVOICE_COMMENT;

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

        $invoiceId = $this->getInvoiceId($rowData);
        if (!$invoiceId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        if (empty($rowData['is_visible_on_front'])) {
            $rowData['is_visible_on_front'] = 0;
        }

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $invoiceId,
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

        $invoiceId = $this->getInvoiceId($rowData) ?: $rowData[static::COLUMN_INVOICE_ID];
        if (!$invoiceId) {
            return false;
        }

        if (empty($rowData['is_visible_on_front'])) {
            $rowData['is_visible_on_front'] = 0;
        }

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $invoiceId,
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
                $this->addRowError(
                    static::ERROR_COLUMN_IS_EMPTY,
                    $rowNumber,
                    $this->prefixCode.':'.$column
                );
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
