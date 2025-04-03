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
 * Class Shipment
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Shipment extends AbstractEntity
{
    /**
     * Entity Id Column Name
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Order Id Column Name
     *
     */
    const COLUMN_ORDER_ID = 'order_id';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'shipmentEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateShipmentEntityId';
    const ERROR_DUPLICATE_INCREMENT_ID = 'duplicateShipmentIncrementId';
    const ERROR_INCREMENT_ID_IS_EMPTY = 'shipmentIncrementIdIsEmpty';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'shipmentEntityIdIsNotExist';
    const ERROR_INCREMENT_ID_IS_EXIST = 'shipmentIncrementIdIsExist';
    const ERROR_INCREMENT_ID_IS_NOT_EXIST = 'shipmentIncrementIdIsNotExist';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'shipmentStoreIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Shipment entity_id is duplicated',
        self::ERROR_DUPLICATE_INCREMENT_ID => 'Shipment increment_id is duplicated',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Shipment entity_id is empty',
        self::ERROR_INCREMENT_ID_IS_EMPTY => 'Shipment increment_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Shipment entity_id is not exist',
        self::ERROR_INCREMENT_ID_IS_EXIST => 'Shipment increment_id is exist',
        self::ERROR_INCREMENT_ID_IS_NOT_EXIST => 'Shipment increment_id is not exist',
        self::ERROR_STORE_ID_IS_NOT_EXIST => 'Shipment store_id is not exist',
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_shipment';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'increment_id',
        'total_qty'
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'increment_id',
        'total_qty'
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_SHIPMENT;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Shipment\Item
     */
    protected $shipmentItem;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Shipment\Track
     */
    protected $shipmentTrack;

    /**
     * @var \Bss\OrderImportExport\Model\Import\Entity\Shipment\Comment
     */
    protected $shipmentComment;

    /**
     * Shipment constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping
     * @param \Bss\OrderImportExport\Helper\Sequence $sequenceHelper
     * @param Shipment\ItemFactory $shipmentItemFactory
     * @param Shipment\TrackFactory $shipmentTrackFactory
     * @param Shipment\CommentFactory $shipmentCommentFactory
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
        \Bss\OrderImportExport\Model\Import\Entity\Shipment\ItemFactory $shipmentItemFactory,
        \Bss\OrderImportExport\Model\Import\Entity\Shipment\TrackFactory $shipmentTrackFactory,
        \Bss\OrderImportExport\Model\Import\Entity\Shipment\CommentFactory $shipmentCommentFactory
    ) {
        $this->shipmentItem = $shipmentItemFactory->create();
        $this->shipmentTrack = $shipmentTrackFactory->create();
        $this->shipmentComment = $shipmentCommentFactory->create();
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
            $this->shipmentItem,
            $this->shipmentTrack,
            $this->shipmentComment
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
                $shipmentIds = $this->getShipmentIdsMapped();
                $orderIds = $this->getOrderIdsMapped();
                $orderItemIds = $this->getItemIdsMapped();
                $child->setOrderIdsMapped($orderIds);
                $child->setItemIdsMapped($orderItemIds);
                $child->setShipmentIdsMapped($shipmentIds);
                $child->importData();
            }
        }

        return true;
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

        $this->setShipmentIdsMapped([]);

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
            $this->resource->getTableName('sales_shipment_grid'),
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
        if ($this->currentShipmentIncrementId) {
            $this->shipmentIdsMapped[$this->currentShipmentIncrementId] = $entityId;
        } else {
            $this->shipmentIdsMapped[$this->getEntityId($rowData)] = $entityId;
        }

        $entityRowData = [
            'order_id' => $this->getOrderId($rowData),
            'entity_id' => $entityId,
            'shipping_address_id' => $this->getShippingAddressId($rowData),
            'billing_address_id' => $this->getBillingAddressId($rowData),
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $updateAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
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
            $this->addRowError(__("The shipment increment_id is exist on other one"), $rowNumber);
            return false;
        }
        $this->newEntities[$rowData[self::COLUMN_INCREMENT_ID]] = $entityId;

        if ($this->currentShipmentIncrementId) {
            $this->shipmentIdsMapped[$this->currentShipmentIncrementId] = $entityId;
        } else {
            $this->shipmentIdsMapped[$this->getEntityId($rowData)] = $entityId;
        }

        $entityRowData = [
            'order_id' => $orderId,
            'entity_id' => $entityId,
            'created_at' => $createdAt->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
            'updated_at' => $now->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT),
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
     * Retrieve Shipment Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistIncrementId(array $rowData)
    {
        $shipmentIdsMapped = $this->getShipmentIdsMapped();
        if (!empty($shipmentIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])) {
            return $shipmentIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]];
        }
        return false;
    }

    /**
     * Retrieve Shipment Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function checkExistEntityId(array $rowData)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            return false;
        }

        $shipmentIdsMapped = $this->getShipmentIdsMappedByEntityId();
        if (!empty($shipmentIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $shipmentIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
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
        }
    }
}
