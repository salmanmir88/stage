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
 * Order Shipment Track Import
 */
class Track extends AbstractEntity
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
     * Order Id Column
     *
     */
    const COLUMN_ORDER_ID = 'order_id';

    /**
     * Track Number Column
     *
     */
    const COLUMN_TRACK_NUMBER = 'track_number';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'shipmentTrackIdIsEmpty';
    const ERROR_SHIPMENT_ID_IS_EMPTY = 'shipmentTrackParentIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateShipmentTrackId';
    const ERROR_ORDER_ID_IS_EMPTY = 'shipmentTrackOrderIdIsEmpty';
    const ERROR_TRACK_NUMBER_IS_EMPTY = 'shipmentTrackNumberIsEmpty';
    const ERROR_SHIPMENT_INCREMENT_ID = 'shipmentTrackIncrementId';
    const ERROR_SHIPMENT_COUNT = 'shipmentItemCount';
    const ERROR_SHIPMENT_IS_EMPTY = 'shipmentItemIsEmpty';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Shipment Track entity_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Shipment Track entity_id is empty',
        self::ERROR_SHIPMENT_ID_IS_EMPTY => 'Shipment Track parent_id is empty',
        self::ERROR_ORDER_ID_IS_EMPTY => 'Shipment Track order_id is empty',
        self::ERROR_TRACK_NUMBER_IS_EMPTY => 'Shipment Track track_number is empty',
        self::ERROR_SHIPMENT_INCREMENT_ID => 'Shipment with selected shipment:increment_id does not exist',
        self::ERROR_SHIPMENT_COUNT => 'Order has more than 1 Shipment. please specify Shipment ID.',
        self::ERROR_SHIPMENT_IS_EMPTY => 'Order does not have Shipment.'
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_shipment_track';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'track_number',
        'carrier_code'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'track_number',
        'carrier_code'
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_SHIPMENT_TRACK;

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

        if (empty($rowData['updated_at'])) {
            $updateAt = $now;
        } else {
            $updateAt = (new \DateTime())->setTimestamp(strtotime($rowData['updated_at']));
        }

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }

        $shipmentId = $this->getShipmentId($rowData);
        if (!$shipmentId) {
            return false;
        }

        $entityId = $this->getNextEntityId();

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $shipmentId,
            'order_id' => $orderId,
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

        $orderId = $this->getOrderId($rowData) ?: $rowData['order_id'];
        if (!$orderId) {
            return false;
        }

        $shipmentId = $this->getShipmentId($rowData) ?: $rowData[self::COLUMN_SHIPMENT_ID];
        if (!$shipmentId) {
            return false;
        }

        $entityRowData = [
            'entity_id' => $entityId,
            'parent_id' => $shipmentId,
            'order_id' => $orderId,
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
