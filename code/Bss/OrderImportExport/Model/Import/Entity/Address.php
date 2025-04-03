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

use Magento\ImportExport\Model\Import;
use Bss\OrderImportExport\Model\Import\Constant;

/**
 * Order Address Import
 */
class Address extends AbstractEntity
{
    /**
     * Address type value
     *
     */
    const COLUMN_ADDRESS_TYPE_BILLING = 'billing';
    const COLUMN_ADDRESS_TYPE_SHIPPING = 'shipping';

    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Parent Id Column
     *
     */
    const COLUMN_PARENT_ID = 'parent_id';

    /**
     * Address Type Column
     *
     */
    const COLUMN_ADDRESS_TYPE = 'address_type';

    /**
     * Country Id Column
     *
     */
    const COLUMN_COUNTRY_ID = 'country_id';

    /**
     * Order Entity Id Column
     *
     */
    const COLUMN_ORDER_ENTITY_ID = 'entity_id';
    const COLUMN_ORDER_BILLING_ADDRESS_ID = 'billing_address_id';
    const COLUMN_ORDER_SHIPPING_ADDRESS_ID = 'shipping_address_id';

    /**
     * Order Item Table Name
     *
     * @var string
     */
    protected $mainTable = 'sales_order_address';

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [
        'entity_id',
        'region_id',
        'region',
        'postcode',
        'firstname',
        'lastname',
        'street',
        'city',
        'email',
        'telephone',
        'country_id',
        'address_type',
    ];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [
        'entity_id',
        'postcode',
        'firstname',
        'lastname',
        'street',
        'city',
        'email',
        'telephone',
        'country_id',
        'address_type',
    ];

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_ADDRESS;

    /**
     * Error Codes
     */
    const ERROR_ADDRESS_TYPE_IS_EMPTY = 'addressTypeIsEmpty';
    const ERROR_COUNTRY_ID_IS_EMPTY = 'countryIdIsEmpty';
    const ERROR_ENTITY_ID_IS_EMPTY = 'addressEntityIdIsEmpty';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateAddressEntityId';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'addressEntityIdIsNotExist';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [
        self::ERROR_DUPLICATE_ENTITY_ID => 'Address entity_id is duplicated in the import file',
        self::ERROR_ADDRESS_TYPE_IS_EMPTY => 'Address address_type is empty',
        self::ERROR_ENTITY_ID_IS_EMPTY => 'Address entity_id is empty',
        self::ERROR_COUNTRY_ID_IS_EMPTY =>'Address country_id is empty',
        self::ERROR_ENTITY_ID_IS_NOT_EXIST => 'Address entity_id is not exist',
    ];

    protected $ordersToValidateAddress = [];

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

        if (!empty($rowData[static::COLUMN_INCREMENT_ID])) {
            $this->ordersToValidateAddress[$this->currentIncrementId]['is_virtual'] = empty($rowData['is_virtual']) ? false : true;
            $this->ordersToValidateAddress[$this->currentIncrementId]['index'] = $rowNumber;
        }

        $rowData = $this->extractFields($rowData, $this->prefixCode);

        if (!empty($rowData[static::COLUMN_ADDRESS_TYPE]) &&
            $rowData[static::COLUMN_ADDRESS_TYPE] == static::COLUMN_ADDRESS_TYPE_BILLING
        ) {
            $this->ordersToValidateAddress[$this->currentIncrementId]['billing'] = true;
        } elseif (!empty($rowData[static::COLUMN_ADDRESS_TYPE]) &&
            $rowData[static::COLUMN_ADDRESS_TYPE] == static::COLUMN_ADDRESS_TYPE_SHIPPING
        ) {
            $this->ordersToValidateAddress[$this->currentIncrementId]['shipping'] = true;
        }

        return (count($rowData) && !$this->isEmptyRow($rowData)) ? $rowData : false;
    }

    public function getOrderToValidate()
    {
        return $this->ordersToValidateAddress;
    }

    public function resetOrderToValidate()
    {
        $this->ordersToValidateAddress = [];
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

        $this->setAddressIdsMapped([]);

        return $this;
    }

    /**
     * Add Entities
     *
     * @return $this
     */
    protected function addAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $entitiesToCreate = [];
            $orderBillingsToUpdate = [];
            $orderShippingsToUpdate = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->extractRowData($rowData);

                // validate entity data
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                $processedData = $this->prepareDataToAdd($rowData, $rowNumber);
                if (!$processedData) {
                    continue;
                }

                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $entitiesToCreate = array_merge($entitiesToCreate, $processedData[self::ENTITIES_TO_CREATE_KEY]);
                $orderBillingsToUpdate = array_merge(
                    $orderBillingsToUpdate,
                    $processedData[self::ORDER_BILLING_TO_UPDATE_KEY]
                );
                $orderShippingsToUpdate = array_merge(
                    $orderShippingsToUpdate,
                    $processedData[self::ORDER_SHIPPING_TO_UPDATE_KEY]
                );
            }

            if ($entitiesToCreate) {
                $this->createEntities($entitiesToCreate);
                $this->updateOrdersAddressId($orderBillingsToUpdate, self::COLUMN_ORDER_BILLING_ADDRESS_ID);
                $this->updateOrdersAddressId($orderShippingsToUpdate, self::COLUMN_ORDER_SHIPPING_ADDRESS_ID);
            }
        }

        return $this;
    }

    /**
     * Update Entities
     *
     * @return $this
     */
    protected function updateAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $entitiesToUpdate = [];

            foreach ($bunch as $rowNumber => $rowData) {
                $rowData = $this->extractRowData($rowData);

                // validate entity data
                if (!$rowData || !$this->validateRow($rowData, $rowNumber)) {
                    continue;
                }

                if ($this->getErrorAggregator()->hasToBeTerminated()) {
                    $this->getErrorAggregator()->addRowToSkip($rowNumber);
                    continue;
                }

                $processedData = $this->prepareDataToUpdate($rowData, $rowNumber);
                if (!$processedData) {
                    continue;
                }

                // phpcs:disable Magento2.Performance.ForeachArrayMerge
                $entitiesToUpdate = array_merge($entitiesToUpdate, $processedData[self::ENTITIES_TO_UPDATE_KEY]);
            }

            if ($entitiesToUpdate) {
                $this->updateEntities($entitiesToUpdate);
            }
        }

        return $this;
    }

    /**
     * Update address id to order table
     *
     * @param $addressData
     * @param $column
     * @return $this
     */
    protected function updateOrdersAddressId($addressData, $column)
    {
        if ($addressData) {
            $this->connection->insertOnDuplicate(
                $this->getOrderTable(),
                $addressData,
                [$column]
            );
        }
        return $this;
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
        $orderBillingsToUpdate = [];
        $orderShippingsToUpdate = [];

        $entityId = $this->getNextEntityId();
        $this->newEntities[$rowData[self::COLUMN_ENTITY_ID]] = $entityId;
        $this->addressIdsMapped[$rowData[static::COLUMN_ENTITY_ID]] = $entityId;

        $orderId = $this->getOrderId($rowData);
        if (!$orderId) {
            return false;
        }

        if (self::COLUMN_ADDRESS_TYPE_BILLING == $rowData[self::COLUMN_ADDRESS_TYPE]) {
            $orderBillingsToUpdate[] = [
                self::COLUMN_ORDER_ENTITY_ID => $orderId,
                self::COLUMN_ORDER_BILLING_ADDRESS_ID => $entityId
            ];
        } elseif (self::COLUMN_ADDRESS_TYPE_SHIPPING == $rowData[self::COLUMN_ADDRESS_TYPE]) {
            $orderShippingsToUpdate[] = [
                self::COLUMN_ORDER_ENTITY_ID => $orderId,
                self::COLUMN_ORDER_SHIPPING_ADDRESS_ID => $entityId
            ];
        }

        $entityRowData = [
            self::COLUMN_PARENT_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId
        ];

        // row data to entity data
        $entityRowData = $this->mergeEntityRow($entityRowData, $rowData);

        $entitiesToCreate[] = $entityRowData;
        return [
            self::ENTITIES_TO_CREATE_KEY => $entitiesToCreate,
            self::ORDER_BILLING_TO_UPDATE_KEY => $orderBillingsToUpdate,
            self::ORDER_SHIPPING_TO_UPDATE_KEY => $orderShippingsToUpdate
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
        $orderId = $this->getOrderId($rowData) ?: $rowData[self::COLUMN_PARENT_ID];
        if (!$orderId) {
            return false;
        }

        if (!$entityId) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            return false;
        }
        $this->addressIdsMapped[$rowData[static::COLUMN_ENTITY_ID]] = $entityId;

        $entityRowData = [
            self::COLUMN_PARENT_ID => $orderId,
            self::COLUMN_ENTITY_ID => $entityId
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

        $addressIdsMapped = $this->getAddressIdsMapped();
        if (!empty($addressIdsMapped[$rowData[static::COLUMN_ENTITY_ID]])) {
            return $addressIdsMapped[$rowData[static::COLUMN_ENTITY_ID]];
        }
        return false;
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
            if (!$this->checkExistEntityId($rowData)) {
                $this->addRowError(static::ERROR_ENTITY_ID_IS_NOT_EXIST, $rowNumber);
            }

            foreach ($this->requiredValueColumns as $column) {
                if (!isset($rowData[$column]) || '' == $rowData[$column]) {
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
        if ($this->validateEntityId($rowData, $rowNumber)) {
            foreach ($this->requiredValueColumns as $column) {
                if (!isset($rowData[$column]) || '' == $rowData[$column]) {
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
