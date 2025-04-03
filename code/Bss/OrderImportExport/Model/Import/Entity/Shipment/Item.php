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
namespace Bss\OrderImportExport\Model\Import\Entity\Shipment;

use Bss\OrderImportExport\Model\Import\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Order Shipment Item Import
 */
class Item extends AbstractEntity
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
    const COLUMN_SHIPMENT_ID = 'parent_id';

    /**
     * Order Item Id Column
     *
     */
    const COLUMN_ORDER_ITEM_ID = 'order_item_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'shipmentItemIdIsEmpty';
    const ERROR_SHIPMENT_ID_IS_EMPTY = 'shipmentItemParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateShipmentItemId';
    const ERROR_ORDER_ITEM_ID_IS_EMPTY = 'shipmentItemOrderItemIdIsEmpty';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Shipment Item entity_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Shipment Item entity_id is empty',
        self::ERROR_SHIPMENT_ID_IS_EMPTY => 'Shipment Item parent_id is empty',
        self::ERROR_ORDER_ITEM_ID_IS_EMPTY => 'Shipment Item order_item_id is empty'
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_shipment_item';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'sku',
        'qty',
        'price',
        'weight',
        'order_item_id'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'sku',
        'qty',
        'price',
        'order_item_id'
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_SHIPMENT_ITEM;

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

        $shipmentId = $this->getShipmentId($rowData);
        if (!$shipmentId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        $shipmentId = $this->getShipmentId($rowData);
        $orderItemId = $this->findOrderItemId($rowData);

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $shipmentId,
            'order_item_id' => $orderItemId
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

        $shipmentId = $this->getShipmentId($rowData) ?: $rowData[self::COLUMN_SHIPMENT_ID];
        if (!$shipmentId) {
            return false;
        }

        $shipmentId = $this->getShipmentId($rowData) ?: $rowData['parent_id'];
        $orderItemId = $this->getOrderItemId($rowData);

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $shipmentId,
            'order_item_id' => $orderItemId
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
            if (empty($rowData[self::COLUMN_SHIPMENT_ID])) {
                $this->addRowError(self::ERROR_SHIPMENT_ID_IS_EMPTY, $rowNumber);
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
