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
use Magento\ImportExport\Model\Import\Entity\AbstractEntity as CoreAbstractEntity;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\ImportExport\Model\Import as ImportExport;
use Magento\ImportExport\Model\Import\AbstractSource;
use Bss\OrderImportExport\Model\Import\Constant;
use Bss\OrderImportExport\Helper\Sequence;
use \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError;

/**
 * Class AbstractEntity
 *
 * @package Bss\OrderImportExport\Model\Import\Entity
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
abstract class AbstractEntity extends CoreAbstractEntity
{
    const ENTITY_TYPE_CODE = "bss_order";

    /**
     * Array Key for Create or Update Entities
     */
    const ENTITIES_TO_CREATE_KEY = 'ENTITIES_TO_CREATE_KEY';
    const ENTITIES_TO_UPDATE_KEY = 'ENTITIES_TO_UPDATE_KEY';
    const ORDER_BILLING_TO_UPDATE_KEY = 'ORDER_BILLING_TO_UPDATE_KEY';
    const ORDER_SHIPPING_TO_UPDATE_KEY = 'ORDER_SHIPPING_TO_UPDATE_KEY';
    const ORDER_STATUS_TO_UPDATE_KEY = 'ORDER_STATUS_TO_UPDATE_KEY';
    const ORDER_STATUS_LABEL_TO_UPDATE_KEY = 'ORDER_STATUS_LABEL_TO_UPDATE_KEY';

    const INCREMENT_ID_SUFFIX = Constant::INCREMENT_ID_SUFFIX;

    /**
     * Entity Id Column
     *
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Order Item Id Column
     *
     */
    const COLUMN_ORDER_ITEM_ID = 'order_item_id';

    /**
     * Shipment Id Column
     *
     */
    const COLUMN_SHIPMENT_ID = 'parent_id';

    /**
     * Payment Id Column
     *
     */
    const COLUMN_PAYMENT_ID = 'payment_id';

    /**
     * Tax Id Column
     *
     */
    const COLUMN_TAX_ID = 'tax_id';

    /**
     * Invoice Id Column
     *
     */
    const COLUMN_INVOICE_ID = 'parent_id';

    /**
     * Creditmemo Id Column
     *
     */
    const COLUMN_CREDITMEMO_ID = 'parent_id';

    /**
     * Increment Id Column
     *
     */
    const COLUMN_INCREMENT_ID = 'increment_id';
    const COLUMN_SHIPMENT_INCREMENT_ID = 'shipment:increment_id';
    const COLUMN_INVOICE_INCREMENT_ID = 'invoice:increment_id';
    const COLUMN_CREDITMEMO_INCREMENT_ID = 'creditmemo:increment_id';

    /**
     * Store Id Column
     *
     */
    const COLUMN_STORE_ID = 'store_id';

    /**
     * State Column
     *
     */
    const COLUMN_STATE = 'state';

    /**
     * Error Codes
     */
    const ERROR_ENTITY_ID_IS_EMPTY = 'entityIdIsEmpty';
    const ERROR_ENTITY_ID_IS_NOT_EXIST = 'entityIdIsNotExist';
    const ERROR_DUPLICATE_ENTITY_ID = 'duplicateEntityId';
    const ERROR_INCREMENT_ID_IS_EMPTY = 'incrementIdIsEmpty';
    const ERROR_INCREMENT_ID_IS_EXIST = 'incrementIdIsExist';
    const ERROR_INCREMENT_ID_IS_NOT_EXIST = 'incrementIdIsNotExist';
    const ERROR_DUPLICATE_INCREMENT_ID = 'duplicateIncrementId';
    const ERROR_STORE_ID_IS_NOT_EXIST = 'storeIdIsNotExist';

    /**
     * Import export data
     *
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $importExportData;

    /**
     * @var \Magento\ImportExport\Model\ResourceModel\Helper
     */
    protected $resourceHelper;

    /**
     * DB connection.
     *
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * DB data source model.
     *
     * @var \Magento\ImportExport\Model\ResourceModel\Import\Data
     */
    protected $dataSourceModel;

    /**
     * Id Of Next Entity Row
     *
     * @var int
     */
    protected $nextEntityId;

    /**
     * Entities Information From Import File
     *
     * @var array
     */
    protected $newEntities = [];

    /**
     * Order Ids Map
     *
     * @var array
     */
    protected $orderIdsMapped;

    /**
     * Order Ids Map
     *
     * @var array
     */
    protected $baseRatesMapped;

    /**
     * Order Ids Map
     *
     * @var array
     */
    protected $taxRatesMapped;

    /**
     * Order Item Ids Map
     *
     * @var array
     */
    protected $itemIdsMapped;

    /**
     * @var Address Ids Map
     */
    protected $addressIdsMapped;

    /**
     * Tax Ids Map
     *
     * @var array
     */
    protected $taxIdsMapped;

    /**
     * Payment Ids Map
     *
     * @var array
     */
    protected $paymentIdsMapped;

    /**
     * Shipment Ids Map
     *
     * @var array
     */
    protected $shipmentIdsMapped;

    /**
     * Invoice Ids Map
     *
     * @var array
     */
    protected $invoiceIdsMapped;

    /**
     * Creditmemo Ids Map
     *
     * @var array
     */
    protected $creditmemoIdsMapped;

    /**
     * Download Link Purchased Ids Map
     *
     * @var array
     */
    protected $downloadLinkIdsMapped;

    /**
     * Order Ids Map By Entity Id
     *
     * @var array
     */
    protected $orderIdsMappedByEntityId;
    protected $shipmentIdsMappedByEntityId;
    protected $invoiceIdsMappedByEntityId;
    protected $creditmemoIdsMappedByEntityId;

    // new error code
    const ERROR_DATA_TYPE_INVALID = 'dataTypeInvalid';
    const ERROR_COLUMN_IS_EMPTY = 'columnIsEmpty';

    /**
     * Validation Custom Message Template
     *
     * @var array
     */
    protected $customMessageTemplates = [];

    /**
     * Validation General Message Template For All Entity
     *
     * @var array
     */
    protected $generalMessageTemplates = [
        self::ERROR_DATA_TYPE_INVALID => "Please correct the data type for '%s'.",
        self::ERROR_COLUMN_IS_EMPTY => "Column '%s' is empty"
    ];

    /**
     * Main Table Name
     *
     * @var string
     */
    protected $mainTable;

    /**
     * Customer Table Name
     *
     * @var string
     */
    protected $customerTable = 'customer_entity';

    /**
     * Store Table Name
     *
     * @var string
     */
    protected $storeTable = 'store';

    /**
     * Order Table Name
     *
     * @var string
     */
    protected $orderTable = 'sales_order';

    /**
     * Order Status Table Name
     *
     * @var string
     */
    protected $orderStatusTable = 'sales_order_status';

    /**
     * Order Status Label Table Name
     *
     * @var string
     */
    protected $orderStatusLabelTable = 'sales_order_status_label';

    /**
     * Order State Table Name
     *
     * @var string
     */
    protected $orderStateTable = 'sales_order_status_state';

    /**
     * Product Table Name
     *
     * @var string
     */
    protected $productTable = 'catalog_product_entity';

    /**
     * Resource Connection
     *
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resource;

    /**
     * Current Order Increment Id
     *
     * @var string
     */
    protected $currentIncrementId;

    /**
     * Current Shipment Increment Id
     *
     * @var string
     */
    protected $currentShipmentIncrementId;

    /**
     * Current Invoice Increment Id
     *
     * @var string
     */
    protected $currentInvoiceIncrementId;

    /**
     * Current Creditmemo Increment Id
     *
     * @var string
     */
    protected $currentCreditmemoIncrementId;

    /**
     * All Columns of Entity Table
     *
     * @var array|string|null
     */
    protected $tableFields;

    /**
     * All Columns Schema Information of Entity Table
     *
     * @var array|string|null
     */
    protected $tableFieldsSchema;

    /**
     * @var bool
     */
    protected $needColumnCheck = true;

    /**
     * prefix text on csv header
     *
     * @var string
     */
    protected $prefixCode = '';

    /**
     * Real Csv Columns For Each Entity
     *
     * @var array|string|null
     */
    protected $entityColumns;

    /**
     * Custom Csv Column For Entity
     *
     * @var array
     */
    protected $customColumns = [];

    /**
     * Required column names
     *
     * @array
     */
    protected $permanentAttributes = [];

    /**
     * List columns which has multiple value
     *
     * @var array
     */
    protected $multipleValueColumns = [];

    /**
     * List columns which has required value
     *
     * @var array
     */
    protected $requiredValueColumns = [];

    /**
     * List exist column in csv file
     *
     * @var array
     */
    protected $existColumns = [];

    /**
     * @var \Bss\OrderImportExport\Model\Import\Mapping\Mapping
     */
    protected $mapping;

    /**
     * Exist Store
     *
     * @var array|null
     */
    protected $stores = null;

    /**
     * Exist Status
     *
     * @var null
     */
    protected $status = null;

    /**
     * Exist States
     *
     * @var null
     */
    protected $states = null;

    /**
     * Check entity on database
     *
     * @var array
     */
    protected $dbEntityIds = [];

    /**
     * Exist customer
     *
     * @var array
     */
    protected $customerIds = [];

    /**
     * Exist customer group
     *
     * @var array
     */
    protected $customerGroupIds = [];

    /**
     * Exist product
     *
     * @var array
     */
    protected $productIds = [];

    /**
     * @var \Bss\OrderImportExport\Helper\Sequence
     */
    protected $sequenceHelper;

    /**
     * @var mixed
     */
    protected $currentBunch = null;

    /**
     * AbstractEntity constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping
     * @param Sequence $sequenceHelper
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping,
        \Bss\OrderImportExport\Helper\Sequence $sequenceHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->importExportData = $importExportData;
        $this->resourceHelper = $resourceHelper;
        $this->errorAggregator = $errorAggregator;

        $this->errorMessageTemplates = array_merge(
            $this->errorMessageTemplates,
            $this->customMessageTemplates,
            $this->generalMessageTemplates
        );
        foreach ($this->errorMessageTemplates as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }

        $this->resource = $resource;
        $this->dataSourceModel = $importData;
        $this->connection = $resource->getConnection();

        $this->mapping = $mapping;
        $this->initDefaultMapping();
        $this->sequenceHelper = $sequenceHelper;
    }

    /**
     * Prepare default mapping for validate
     */
    public function initDefaultMapping()
    {
        $this->orderIdsMapped = $this->mapping->getMapped();
        $this->itemIdsMapped = $this->mapping->getMapped(Constant::PREFIX_ORDER_ITEM);
        $this->addressIdsMapped = $this->mapping->getMapped(Constant::PREFIX_ORDER_ADDRESS);
        $this->taxIdsMapped = $this->mapping->getMapped(Constant::PREFIX_ORDER_TAX);
        $this->paymentIdsMapped = $this->mapping->getMapped(Constant::PREFIX_ORDER_PAYMENT);
        $this->shipmentIdsMapped = $this->mapping->getMapped(Constant::PREFIX_SHIPMENT);
        $this->invoiceIdsMapped = $this->mapping->getMapped(Constant::PREFIX_INVOICE);
        $this->creditmemoIdsMapped = $this->mapping->getMapped(Constant::PREFIX_CREDITMEMO);

        $this->orderIdsMappedByEntityId = $this->mapping->getMapped(Constant::MAPPING_ORDER_BY_ENTITY_ID_KEY);
        $this->shipmentIdsMappedByEntityId = $this->mapping->getMapped(Constant::MAPPING_SHIPMENT_BY_ENTITY_ID_KEY);
        $this->invoiceIdsMappedByEntityId = $this->mapping->getMapped(Constant::MAPPING_INVOICE_BY_ENTITY_ID_KEY);
        $this->creditmemoIdsMappedByEntityId = $this->mapping->getMapped(Constant::MAPPING_CREDITMEMO_BY_ENTITY_ID_KEY);

        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->initDefaultMapping();
            }
        }
    }

    /**
     * Retrieve Order Ids Map
     *
     * @return array
     */
    public function getOrderIdsMapped()
    {
        return $this->orderIdsMapped ?: [];
    }

    /**
     * Get Base Rate Map
     *
     * @return array
     */
    public function getBaseRatesMapped()
    {
        return $this->baseRatesMapped ?: [];
    }

    /**
     * Get Tax Rate Map
     *
     * @return array
     */
    public function getTaxRatesMapped()
    {
        return $this->taxRatesMapped ?: [];
    }

    /**
     * Retrieve Order Item Ids Map
     *
     * @return array
     */
    public function getItemIdsMapped()
    {
        return $this->itemIdsMapped ?: [];
    }

    /**
     * Get Address Id Map
     *
     * @return array
     */
    public function getAddressIdsMapped()
    {
        return $this->addressIdsMapped ?: [];
    }

    /**
     * Set Order Ids Map
     *
     * @param array $orderIds
     */
    public function setOrderIdsMapped(array $orderIds)
    {
        $this->orderIdsMapped = $orderIds;
    }

    /**
     * Set Base Rate Map
     *
     * @param array $baseRates
     */
    public function setBaseRatesMapped(array $baseRates)
    {
        $this->baseRatesMapped = $baseRates;
    }

    /**
     * Set Tax Rate Map
     *
     * @param array $taxRates
     */
    public function setTaxRatesMapped(array $taxRates)
    {
        $this->taxRatesMapped = $taxRates;
    }

    /**
     * Set Order Item Ids Map
     *
     * @param array $itemIds
     */
    public function setitemIdsMapped(array $itemIds)
    {
        $this->itemIdsMapped = $itemIds;
    }

    /**
     * Set Address Id Map
     *
     * @param array $addressIds
     */
    public function setAddressIdsMapped(array $addressIds)
    {
        $this->addressIdsMapped = $addressIds;
    }

    /**
     * Retrieve Shipment Ids Map
     *
     * @return array
     */
    public function getShipmentIdsMapped()
    {
        return $this->shipmentIdsMapped ?: [];
    }

    /**
     * Set Shipment Ids Map
     *
     * @param array $shipmentIds
     * @return $this
     */
    public function setShipmentIdsMapped(array $shipmentIds)
    {
        $this->shipmentIdsMapped = $shipmentIds;

        return $this;
    }

    /**
     * Retrieve Payment Ids Map
     *
     * @return array
     */
    public function getPaymentIdsMapped()
    {
        return $this->paymentIdsMapped ?: [];
    }

    /**
     * Set Payment Ids Map
     *
     * @param array $paymentIds
     * @return $this
     */
    public function setPaymentIdsMapped(array $paymentIds)
    {
        $this->paymentIdsMapped = $paymentIds;

        return $this;
    }

    /**
     * Retrieve Tax Ids Map
     *
     * @return array
     */
    public function getTaxIdsMapped()
    {
        return $this->taxIdsMapped ?: [];
    }

    /**
     * Set Tax Ids Map
     *
     * @param array $taxIds
     * @return $this
     */
    public function setTaxIdsMapped(array $taxIds)
    {
        $this->taxIdsMapped = $taxIds;

        return $this;
    }

    /**
     * Retrieve Invoice Id Map
     *
     * @return array|null
     */
    public function getInvoiceIdsMapped()
    {
        return $this->invoiceIdsMapped ?: [];
    }

    /**
     * Set Invoice Ids Map
     *
     * @param array $invoiceIds
     * @return $this
     */
    public function setInvoiceIdsMapped(array $invoiceIds)
    {
        $this->invoiceIdsMapped = $invoiceIds;

        return $this;
    }

    /**
     * Retrieve Creditmemo Id Map
     *
     * @return array
     */
    public function getCreditmemoIdsMapped()
    {
        return $this->creditmemoIdsMapped ?: [];
    }

    /**
     * Set Creditmemo Ids Map
     *
     * @param array $creditmemoIds
     * @return $this
     */
    public function setCreditmemoIdsMapped(array $creditmemoIds)
    {
        $this->creditmemoIdsMapped = $creditmemoIds;

        return $this;
    }

    /**
     * Retrieve Download Link Purchased Ids Map
     *
     * @return array
     */
    public function getDownloadLinkIdsMapped()
    {
        return $this->downloadLinkIdsMapped ?: [];
    }

    /**
     * Set Download Link Purchased Ids Map
     *
     * @param array $purchasedIds
     * @return $this
     */
    public function setDownloadLinkIdsMapped(array $purchasedIds)
    {
        $this->downloadLinkIdsMapped = $purchasedIds;

        return $this;
    }

    /**
     * Retrieve Order Ids Map By Entity Id
     *
     * @return array
     */
    public function getOrderIdsMappedByEntityId()
    {
        return $this->orderIdsMappedByEntityId ?: [];
    }

    /**
     * Retrieve Shipment Ids Map By Entity Id
     *
     * @return array
     */
    public function getShipmentIdsMappedByEntityId()
    {
        return $this->shipmentIdsMappedByEntityId ?: [];
    }

    /**
     * Retrieve Invoice Ids Map By Entity Id
     *
     * @return array
     */
    public function getInvoiceIdsMappedByEntityId()
    {
        return $this->invoiceIdsMappedByEntityId ?: [];
    }

    /**
     * Retrieve Creditmemo Ids Map By Entity Id
     *
     * @return array
     */
    public function getCreditmemoIdsMappedByEntityId()
    {
        return $this->creditmemoIdsMappedByEntityId ?: [];
    }

    /**
     * Import Behavior Getter
     *
     * @return string
     */
    public function getBehavior()
    {
        if (!isset($this->_parameters['behavior']) ||
            $this->_parameters['behavior'] != Import::BEHAVIOR_APPEND &&
            $this->_parameters['behavior'] != Constant::BEHAVIOR_UPDATE &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_DELETE
        ) {
            return Import::getDefaultBehavior();
        }
        return $this->_parameters['behavior'];
    }

    /**
     * Import Data Rows
     *
     * @return boolean
     * @throws \Zend_Db_Statement_Exception
     */
    protected function _importData()
    {
        if (Import::BEHAVIOR_DELETE == $this->getBehavior()) {
            $this->deleteAction();
        } elseif (Constant::BEHAVIOR_UPDATE == $this->getBehavior()) {
            $this->updateAction();
        } elseif (Import::BEHAVIOR_APPEND== $this->getBehavior()) {
            $this->addAction();
        }
        return true;
    }

    /**
     * @param $bunch
     */
    public function setCurrentBunch($bunch)
    {
        $this->currentBunch = $bunch;
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->setCurrentBunch($bunch);
            }
        }
    }

    /**
     * @return mixed
     */
    protected function getCurrentBunch()
    {
        return $this->currentBunch;
    }

    /**
     * Delete Entities
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    protected function deleteAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $idsToDelete = [];

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

                $idsToDelete[] = $this->getIdToDelete($rowData);
            }

            if ($idsToDelete) {
                $this->deleteEntities($idsToDelete);
            }
        }

        return $this;
    }

    /**
     * Add Entities
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
     */
    protected function addAction()
    {
        if ($bunch = $this->getCurrentBunch()) {
            $entitiesToCreate = [];
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
            }

            if ($entitiesToCreate) {
                $this->createEntities($entitiesToCreate);
            }
        }

        return $this;
    }

    /**
     * Update Entities
     *
     * @return $this
     * @throws \Zend_Db_Statement_Exception
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

        return $this;
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
        if (!empty($rowData[static::COLUMN_INCREMENT_ID])) {
            if ($this->mustAddOrderIncrementIdSuffix($rowData)) {
                $rowData[static::COLUMN_INCREMENT_ID] =
                    $rowData[static::COLUMN_INCREMENT_ID] . static::INCREMENT_ID_SUFFIX;
            }
            $this->currentIncrementId = $rowData[static::COLUMN_INCREMENT_ID];
        }

        if (!empty($rowData[static::COLUMN_SHIPMENT_INCREMENT_ID])) {
            if ($this->mustAddShipmentIncrementIdSuffix($rowData)) {
                $rowData[static::COLUMN_SHIPMENT_INCREMENT_ID] =
                    $rowData[static::COLUMN_SHIPMENT_INCREMENT_ID] . static::INCREMENT_ID_SUFFIX;
            }
            $this->currentShipmentIncrementId = $rowData[static::COLUMN_SHIPMENT_INCREMENT_ID];
        }

        if (!empty($rowData[static::COLUMN_INVOICE_INCREMENT_ID])) {
            if ($this->mustAddInvoiceIncrementIdSuffix($rowData)) {
                $rowData[static::COLUMN_INVOICE_INCREMENT_ID] =
                    $rowData[static::COLUMN_INVOICE_INCREMENT_ID] . static::INCREMENT_ID_SUFFIX;
            }
            $this->currentInvoiceIncrementId = $rowData[static::COLUMN_INVOICE_INCREMENT_ID];
        }

        if (!empty($rowData[static::COLUMN_CREDITMEMO_INCREMENT_ID])) {
            if ($this->mustAddCreditmemoIncrementIdSuffix($rowData)) {
                $rowData[static::COLUMN_CREDITMEMO_INCREMENT_ID] =
                    $rowData[static::COLUMN_CREDITMEMO_INCREMENT_ID] . static::INCREMENT_ID_SUFFIX;
            }
            $this->currentCreditmemoIncrementId = $rowData[static::COLUMN_CREDITMEMO_INCREMENT_ID];
        }

        return $rowData;
    }

    /**
     * @param $rowData
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    protected function mustAddOrderIncrementIdSuffix($rowData)
    {
        if (Constant::BEHAVIOR_UPDATE == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_ORDER,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                $entityId = isset($this->orderIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])
                    ? $this->orderIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]]
                    : false;
                if (!$entityId ||
                    (!empty($rowData[static::COLUMN_ENTITY_ID]) && $entityId != $rowData[static::COLUMN_ENTITY_ID])
                ) {
                    return true;
                }
            }
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_ORDER,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $rowData
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    protected function mustAddShipmentIncrementIdSuffix($rowData)
    {
        if (Constant::BEHAVIOR_UPDATE == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_SHIPMENT,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                $entityId = isset($this->shipmentIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])
                    ? $this->shipmentIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]]
                    : false;
                if (!$entityId ||
                    (!empty($rowData[static::COLUMN_ENTITY_ID]) && $entityId != $rowData[static::COLUMN_ENTITY_ID])
                ) {
                    return true;
                }
            }
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_SHIPMENT,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $rowData
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    protected function mustAddInvoiceIncrementIdSuffix($rowData)
    {
        if (Constant::BEHAVIOR_UPDATE == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_INVOICE,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                $entityId = isset($this->invoiceIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])
                    ? $this->invoiceIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]]
                    : false;
                if (!$entityId ||
                    (!empty($rowData[static::COLUMN_ENTITY_ID]) && $entityId != $rowData[static::COLUMN_ENTITY_ID])
                ) {
                    return true;
                }
            }
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_INVOICE,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param $rowData
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    protected function mustAddCreditmemoIncrementIdSuffix($rowData)
    {
        if (Constant::BEHAVIOR_UPDATE == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_CREDITMEMO,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                $entityId = isset($this->creditmemoIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]])
                    ? $this->creditmemoIdsMapped[$rowData[static::COLUMN_INCREMENT_ID]]
                    : false;
                if (!$entityId ||
                    (!empty($rowData[static::COLUMN_ENTITY_ID]) && $entityId != $rowData[static::COLUMN_ENTITY_ID])
                ) {
                    return true;
                }
            }
        } elseif (Import::BEHAVIOR_APPEND == $this->getBehavior()) {
            if (!empty($rowData[static::COLUMN_STORE_ID]) &&
                $this->sequenceHelper->isMagentoIncrementId(
                    $rowData[static::COLUMN_INCREMENT_ID],
                    Sequence::TYPE_CREDITMEMO,
                    $rowData[static::COLUMN_STORE_ID]
                )
            ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @return ProcessingErrorAggregatorInterface|void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateData()
    {
        if ($this->getEntityColumns()) {
            // do all permanent columns exist?
            $absentColumns = [];
            if ($this->getBehavior() != Import::BEHAVIOR_DELETE) {
                $absentColumns = array_diff($this->requiredValueColumns, $this->getEntityColumns());
                foreach ($absentColumns as &$columnName) {
                    $columnName = $this->prefixCode?$this->prefixCode.":".$columnName:$columnName;
                }
            }
            $this->addErrors(self::ERROR_CODE_COLUMN_NOT_FOUND, $absentColumns);

            if (ImportExport::BEHAVIOR_DELETE != $this->getBehavior()) {
                // check attribute columns names validity
                $columnNumber = 0;
                $emptyHeaderColumns = [];
                $invalidColumns = [];
                $invalidAttributes = [];
                foreach ($this->getEntityColumns() as $columnName) {
                    $columnNumber++;
                    if (!$this->isAttributeParticular($columnName)) {
                        if (trim($columnName) == '') {
                            $emptyHeaderColumns[] = $columnNumber;
                        } elseif (!preg_match('/^[a-z][a-z0-9_]*$/', $columnName)) {
                            $invalidColumns[] = $this->prefixCode?$this->prefixCode.":".$columnName:$columnName;
                        } elseif ($this->needColumnCheck && !in_array($columnName, $this->getValidColumnNames())) {
                            $invalidAttributes[] = $this->prefixCode?$this->prefixCode.":".$columnName:$columnName;
                        }
                    }
                }
                $this->addErrors(self::ERROR_CODE_INVALID_ATTRIBUTE, $invalidAttributes);
                $this->addErrors(self::ERROR_CODE_COLUMN_EMPTY_HEADER, $emptyHeaderColumns);
                $this->addErrors(self::ERROR_CODE_COLUMN_NAME_INVALID, $invalidColumns);
            }

            // validate for child entity
            if ($this->getChildren()) {
                foreach ($this->getChildren() as $child) {
                    $child->validateData();
                }
            }
        }
    }

    /**
     * Validate Row Data Type
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    public function validateRowDataType($rowData, $rowNumber)
    {
        foreach ($rowData as $column => $value) {
            if (!$this->validateColumnDataType($column, $value)) {
                $this->addRowError(
                    self::ERROR_DATA_TYPE_INVALID,
                    $rowNumber,
                    $column
                );
            }
        }
    }

    /**
     * @param $column
     * @param $value
     * @return bool
     */
    public function validateColumnDataType($column, $value)
    {
        if (!$value) {
            return true;
        }

        $result = true;
        $fields = $this->getFieldsInfo();
        $fieldInfo = isset($fields[$column]) ? $fields[$column] : false;
        if ($fieldInfo) {
            $dataType = isset($fieldInfo['DATA_TYPE']) ? $fieldInfo['DATA_TYPE'] : false;
            switch ($dataType) {
                case "int":
                    if (!is_numeric($value) || floor($value) != $value) {
                        $result = false;
                    }
                    break;
                case "smallint":
                    if (!is_numeric($value) || floor($value) != $value) {
                        $result = false;
                    }
                    break;
                case "decimal":
                    if (!is_numeric($value)) {
                        $result = false;
                    }
                    break;
                case "varchar":
                    break;
                case "text":
                    break;
                case "mediumblob":
                    break;
                case "blob":
                    break;
                case "datetime":
                    $date = \DateTime::createFromFormat('Y-m-d', $value);
                    $datetime = \DateTime::createFromFormat('Y-m-d H:i:s', $value);
                    if (!$date && !$datetime) {
                        return false;
                    }
                    break;
                default:
                    break;
            }
        }

        return $result;
    }

    /**
     * @return array
     */
    public function getFieldsInfo()
    {
        if (!$this->tableFieldsSchema) {
            $this->tableFieldsSchema = $this->connection->describeTable(
                $this->resource->getTableName($this->mainTable)
            );
        }
        return $this->tableFieldsSchema;
    }

    /**
     * @return array
     */
    public function getValidColumnNames()
    {
        if (!$this->validColumnNames) {
            $this->validColumnNames = $this->getMainTableFields();
        }
        return array_unique(
            array_merge(
                $this->validColumnNames,
                $this->customColumns
            )
        );
    }

    /**
     * Is Empty Row
     *
     * @param array $rowData
     * @return bool
     */
    public function isEmptyRow($rowData)
    {
        if ($this->getBehavior() == Import::BEHAVIOR_DELETE && !empty($rowData['entity_id'])) {
            return false;
        }

        // check if all field null
        $empty = true;
        foreach ($this->getMainTableFields() as $field) {
            if (!empty($rowData[$field]) && $field != static::COLUMN_ENTITY_ID) {
                $empty = false;
                break;
            }
        }
        return $empty;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getEntityColumns()
    {
        if (!$this->entityColumns) {
            $columns = $this->getSource()->getColNames();
            $this->entityColumns = [];
            foreach ($columns as $columnName) {
                if ($this->prefixCode && strpos($columnName, ':') !== false) {
                    list($fieldPrefix, $field) = explode(':', $columnName);
                    if ($fieldPrefix == $this->prefixCode) {
                        $this->entityColumns[] = $field;
                    }
                } elseif (!$this->prefixCode && strpos($columnName, ':') === false) {
                    $this->entityColumns[] = $columnName;
                }
            }
        }
        return $this->entityColumns;
    }

    /**
     * Retrieve Extracted Field
     *
     * @param array $rowData
     * @param string $prefix
     * @return array|bool
     */
    protected function extractFields($rowData, $prefix)
    {
        $data = [];
        foreach ($rowData as $field => $value) {
            if ($prefix && strpos($field, ':') !== false) {
                list($fieldPrefix, $field) = explode(':', $field);
                if ($fieldPrefix == $prefix) {
                    $data[$field] = $value;
                }
            } elseif (!$this->prefixCode && strpos($field, ':') === false) {
                $data[$field] = $value;
            }
        }

        $this->existColumns = array_keys($data);

        return $data;
    }

    /**
     * Retrieve Entity Id If Entity Is Present In Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getEntityId(array $rowData)
    {
        return $rowData[static::COLUMN_ENTITY_ID];
    }

    /**
     * Retrieve Order Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getOrderId(array $rowData)
    {
        if (null !== $this->orderIdsMapped) {
            /**
             * current increment id
             */
            $incId = $this->currentIncrementId;
            if (!$incId) {
                $incId = !empty($rowData[static::COLUMN_INCREMENT_ID]) ? $rowData[static::COLUMN_INCREMENT_ID] : 0;
            }
            if (isset($this->orderIdsMapped[$incId])) {
                return $this->orderIdsMapped[$incId];
            }
        }
        return false;
    }

    /**
     * Retrieve Base Rate From Calculation
     *
     * @param array $rowData
     * @return int
     */
    protected function getBaseRate(array $rowData)
    {
        if (null !== $this->baseRatesMapped) {
            /**
             * current increment id
             */
            $incId = $this->currentIncrementId;
            if (!$incId) {
                $incId = !empty($rowData[static::COLUMN_INCREMENT_ID]) ? $rowData[static::COLUMN_INCREMENT_ID] : 0;
            }
            if (isset($this->baseRatesMapped[$incId])) {
                return $this->baseRatesMapped[$incId];
            }
        }
        return 1;
    }

    /**
     * Retrieve Tax Rate From Calculation
     *
     * @param array $rowData
     * @return int
     */
    protected function getTaxRate(array $rowData)
    {
        if (null !== $this->taxRatesMapped) {
            /**
             * current increment id
             */
            $incId = $this->currentIncrementId;
            if (!$incId) {
                $incId = !empty($rowData[static::COLUMN_INCREMENT_ID]) ? $rowData[static::COLUMN_INCREMENT_ID] : 0;
            }
            if (isset($this->taxRatesMapped[$incId])) {
                return $this->taxRatesMapped[$incId];
            }
        }
        return 0;
    }

    /**
     * Retrieve Order Item Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getOrderItemId(array $rowData)
    {
        if (null !== $this->itemIdsMapped) {
            $itemId = $rowData[static::COLUMN_ORDER_ITEM_ID];
            if (isset($this->itemIdsMapped[$itemId])) {
                return $this->itemIdsMapped[$itemId];
            }
        }
        return false;
    }

    /**
     * Retrieve Address Id From Database
     *
     * @param string|int $addressId
     * @return bool|int
     */
    protected function getAddressId($addressId)
    {
        if (null !== $this->addressIdsMapped) {
            if (isset($this->addressIdsMapped[$addressId])) {
                return $this->addressIdsMapped[$addressId];
            }
        }
        return null;
    }

    /**
     * Retrieve Shipment Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getShipmentId(array $rowData)
    {
        if (null !== $this->shipmentIdsMapped) {
            /**
             * current increment id
             */
            $shipmentId = $this->currentShipmentIncrementId;
            if (!$shipmentId) {
                $shipmentId = !empty($rowData[static::COLUMN_SHIPMENT_ID]) ? $rowData[static::COLUMN_SHIPMENT_ID] : 0;
            }
            if (isset($this->shipmentIdsMapped[$shipmentId])) {
                return $this->shipmentIdsMapped[$shipmentId];
            }
        }
        return false;
    }

    /**
     * Retrieve Payment Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getPaymentId(array $rowData)
    {
        if (null !== $this->paymentIdsMapped) {
            $paymentId = $rowData[static::COLUMN_PAYMENT_ID];
            if (isset($this->paymentIdsMapped[$paymentId])) {
                return $this->paymentIdsMapped[$paymentId];
            }
        }
        return false;
    }

    /**
     * Retrieve Tax Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getTaxId(array $rowData)
    {
        if (null !== $this->taxIdsMapped) {
            $taxId = $rowData[static::COLUMN_TAX_ID];
            if (isset($this->taxIdsMapped[$taxId])) {
                return $this->taxIdsMapped[$taxId];
            }
        }
        return false;
    }

    /**
     * Retrieve Invoice Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getInvoiceId(array $rowData)
    {
        if (null !== $this->invoiceIdsMapped) {
            /**
             * current increment id
             */
            $invoiceId = $this->currentInvoiceIncrementId;
            if (!$invoiceId) {
                $invoiceId = !empty($rowData[static::COLUMN_INVOICE_ID]) ? $rowData[static::COLUMN_INVOICE_ID] : 0;
            }
            if (isset($this->invoiceIdsMapped[$invoiceId])) {
                return $this->invoiceIdsMapped[$invoiceId];
            }
        }
        return false;
    }

    /**
     * Retrieve Creditmemo Id From Database
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getCreditmemoId(array $rowData)
    {
        if (null !== $this->creditmemoIdsMapped) {
            /**
             * current increment id
             */
            $creditmemoId = $this->currentCreditmemoIncrementId;
            if (!$creditmemoId) {
                $creditmemoId = !empty($rowData[static::COLUMN_CREDITMEMO_ID])
                    ? $rowData[static::COLUMN_CREDITMEMO_ID]
                    : 0;
            }
            if (isset($this->creditmemoIdsMapped[$creditmemoId])) {
                return $this->creditmemoIdsMapped[$creditmemoId];
            }
        }
        return false;
    }

    /**
     * Prepare Data To Add Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     */
    abstract protected function prepareDataToAdd(array $rowData, $rowNumber);

    /**
     * Prepare Data To Update Entities
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return array|bool
     */
    abstract protected function prepareDataToUpdate(array $rowData, $rowNumber);

    /**
     * Retrieve Id To Delete
     *
     * @param array $rowData
     * @return string
     */
    protected function getIdToDelete(array $rowData)
    {
        return $this->getEntityId($rowData);
    }

    /**
     * Insert Data In Entity Table
     *
     * @param array $entitiesToCreate Rows for insert
     * @return $this
     */
    protected function createEntities(array $entitiesToCreate)
    {
        try {
            if ($entitiesToCreate) {
                if (!$this->prefixCode) {
                    $this->countItemsCreated += count($entitiesToCreate);
                }
                $this->connection->insertMultiple(
                    $this->getMainTable(),
                    $entitiesToCreate
                );
            }
        } catch (\Exception $exception) {
            $this->getErrorAggregator()->addError($exception->getMessage(), ProcessingError::ERROR_LEVEL_CRITICAL);
        }

        return $this;
    }

    /**
     * Insert Data In Entity Table
     *
     * @param array $entitiesToUpdate Rows for update
     * @return $this
     */
    protected function updateEntities(array $entitiesToUpdate)
    {
        try {
            if ($entitiesToUpdate) {
                if (!$this->prefixCode) {
                    $this->countItemsUpdated += count($entitiesToUpdate);
                }
                $this->connection->insertOnDuplicate(
                    $this->getMainTable(),
                    $entitiesToUpdate,
                    $this->getEntityFieldsToUpdate($entitiesToUpdate)
                );
            }
        } catch (\Exception $exception) {
            $this->getErrorAggregator()->addError($exception->getMessage(), ProcessingError::ERROR_LEVEL_CRITICAL);
        }

        return $this;
    }

    /**
     * Filter The Entity That Are Being Updated So We Only Change Fields Found In The Importer File
     *
     * @param array $entitiesToUpdate
     * @return array
     */
    protected function getEntityFieldsToUpdate(array $entitiesToUpdate)
    {
        $firstEntity = reset($entitiesToUpdate);
        $columnsToUpdate = array_keys($firstEntity);
        $fieldsToUpdate = array_filter(
            $this->getMainTableFields(),
            function ($field) use ($columnsToUpdate) {
                return in_array($field, $columnsToUpdate);
            }
        );
        return $fieldsToUpdate;
    }

    /**
     * Delete List Of Entities
     *
     * @param array $idsToDelete Entities Id List
     * @return $this
     */
    protected function deleteEntities(array $idsToDelete)
    {
        if (!$this->prefixCode) {
            $this->countItemsDeleted += count($idsToDelete);
        }
        $cond = $this->connection->quoteInto(
            static::COLUMN_ENTITY_ID . ' IN (?)',
            $idsToDelete
        );
        $this->connection->delete($this->getMainTable(), $cond);

        return $this;
    }

    /**
     * Merge row data to entity data
     *
     * @param array $entityRow
     * @param array $rowData
     * @return array
     */
    protected function mergeEntityRow(array $entityRow, array $rowData)
    {
        $keys = array_keys($entityRow);
        foreach ($this->getMainTableFields() as $field) {
            if (!in_array($field, $keys)) {
                $entityRow[$field] = isset($rowData[$field]) ? $rowData[$field] : null;
            }
        }
        return $entityRow;
    }

    /**
     * Retrieve All Entity Table Columns
     *
     * @return array
     */
    public function getMainTableFields()
    {
        if (!$this->tableFields) {
            $this->tableFields = array_keys($this->getFieldsInfo());
        }

        return $this->tableFields;
    }

    /**
     * @param $rowData
     * @return bool
     */
    protected function checkExistIncrementIdOnOtherOne($rowData)
    {
        $incrementId = $rowData[static::COLUMN_INCREMENT_ID];
        $csvEntityId = $rowData[static::COLUMN_ENTITY_ID];
        if (empty($this->dbEntityIds[$incrementId])) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from($this->getMainTable(), static::COLUMN_ENTITY_ID)
                ->where('increment_id = :increment_id');

            $bind = [':increment_id' => (string)$incrementId];

            $this->dbEntityIds[$incrementId] = $this->connection->fetchOne($select, $bind);
        }

        if ($this->dbEntityIds[$incrementId] && $this->dbEntityIds[$incrementId] != $csvEntityId) {
            return true;
        }

        return false;
    }

    /**
     * Get All Exist Store
     *
     * @return array|null
     * @throws \Zend_Db_Statement_Exception
     */
    public function getExistStores()
    {
        if (null == $this->stores) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from(['e' => $this->getStoreTable()], ['store_id', 'name']);
            $result = $this->connection->query($select);
            while ($row = $result->fetch()) {
                $this->stores[$row['store_id']] = $row['name'];
            }
        }

        return $this->stores;
    }

    /**
     * Get All Exist Status
     *
     * @return array|null
     * @throws \Zend_Db_Statement_Exception
     */
    public function getExistStatus()
    {
        if (null == $this->status) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from(['e' => $this->getOrderStatusTable()], ['status', 'label']);
            $result = $this->connection->query($select);
            while ($row = $result->fetch()) {
                $this->status[$row['status']] = $row['label'];
            }
        }

        return $this->status;
    }

    /**
     * Get All Exist States
     *
     * @return array|null
     * @throws \Zend_Db_Statement_Exception
     */
    public function getExistStates()
    {
        if (null == $this->states) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from(['e' => $this->getOrderStateTable()], ['state']);
            $result = $this->connection->query($select);
            while ($row = $result->fetch()) {
                $this->states[] = $row['state'];
            }
        }

        return $this->states;
    }

    /**
     * Retrieve Customer Id
     *
     * @param $email
     * @param int $storeId
     * @return mixed|null
     */
    public function getCustomerId($email, $storeId = 0)
    {
        if (empty($this->customerIds[$email])) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from(['e' => $this->getCustomerTable()], ['e.entity_id', 'e.group_id'])
                ->join(
                    ['s' => $this->getStoreTable()],
                    'e.website_id=s.website_id',
                    []
                )
                ->where('e.email = :email')
                ->where('s.store_id = :store_id');

            $bind = [':email' => $email, ':store_id' => $storeId];
            $rowData = $this->connection->fetchRow($select, $bind);
            if ($rowData) {
                $this->customerIds[$email] = $rowData['entity_id'];
                $this->customerGroupIds[$email] = $rowData['group_id'];
            } else {
                $this->customerIds[$email] = null;
            }
        }
        return !empty($this->customerIds[$email]) ? $this->customerIds[$email] : null;
    }

    /**
     * Retrieve Customer Group Id
     *
     * @param $customerEmail
     * @return mixed|null
     */
    public function getCustomerGroupId($customerEmail)
    {
        return !empty($this->customerGroupIds[$customerEmail]) ? $this->customerGroupIds[$customerEmail] : null;
    }

    /**
     * Retrieve Product Id
     *
     * @param string $sku
     * @return bool|int
     */
    public function getProductIdBySku($sku)
    {
        if (empty($this->productIds[$sku])) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from($this->getProductTable(), 'entity_id')
                ->where('sku = :sku');

            $bind = [':sku' => (string)$sku];

            $this->productIds[$sku] = $this->connection->fetchOne($select, $bind);
        }

        return !empty($this->productIds[$sku]) ? $this->productIds[$sku] : null;
    }

    /**
     * Retrieve Shipping Address Id
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getShippingAddressId($rowData)
    {
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->connection->select();
        $select->from($this->resource->getTableName("sales_order_address"), 'entity_id')
            ->where('parent_id = :parent_id')
            ->where('address_type = :address_type');

        $bind = [
            ':address_type' => 'shipping',
            ':parent_id' => $this->getOrderId($rowData)
        ];

        return $this->connection->fetchOne($select, $bind);
    }

    /**
     * Retrieve Billing Address Id
     *
     * @param array $rowData
     * @return bool|int
     */
    protected function getBillingAddressId($rowData)
    {
        $bind = [
            ':address_type' => 'billing',
            ':parent_id' => $this->getOrderId($rowData)
        ];
        /** @var $select \Magento\Framework\DB\Select */
        $select = $this->connection->select();
        $select->from($this->resource->getTableName("sales_order_address"), 'entity_id')
            ->where('parent_id = :parent_id')
            ->where('address_type = :address_type');

        return $this->connection->fetchOne($select, $bind);
    }

    /**
     * Validate Data Row For Each Entity
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return boolean
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        if (isset($this->_validatedRows[$rowNumber])) {
            // check that row is already validated
            return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
        }
        $this->_validatedRows[$rowNumber] = true;
        $this->_processedEntitiesCount++;

        /* behavior selector */
        switch ($this->getBehavior()) {
            case Import::BEHAVIOR_DELETE:
                $this->validateRowForDelete($rowData, $rowNumber);
                break;
            case Constant::BEHAVIOR_UPDATE:
                $this->validateRowDataType($rowData, $rowNumber);
                $this->validateRowForUpdate($rowData, $rowNumber);
                break;
            case Import::BEHAVIOR_APPEND:
                $this->validateRowDataType($rowData, $rowNumber);
                $this->validateRowForAdd($rowData, $rowNumber);
                break;
        }

        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * Validate Row Data For Replace Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function validateRowForReplace(array $rowData, $rowNumber)
    {
        if ($this->validateEntityId($rowData, $rowNumber)) {
            $entityId = $rowData[static::COLUMN_ENTITY_ID];
            if (isset($this->newEntities[$entityId])) {
                $this->addRowError(static::ERROR_DUPLICATE_ENTITY_ID, $rowNumber);
            }
        }
    }

    /**
     * Validate Row Data For Update Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    abstract protected function validateRowForUpdate(array $rowData, $rowNumber);

    /**
     * Validate Row Data For Add Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    abstract protected function validateRowForAdd(array $rowData, $rowNumber);

    /**
     * Validate Row Data For Delete Behaviour
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return void
     */
    protected function validateRowForDelete(array $rowData, $rowNumber)
    {
        $this->validateEntityId($rowData, $rowNumber);
    }

    /**
     * General Check Of Unique Key
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function validateEntityId(array $rowData, $rowNumber)
    {
        if (empty($rowData[static::COLUMN_ENTITY_ID])) {
            $this->addRowError(static::ERROR_ENTITY_ID_IS_EMPTY, $rowNumber);
        }
        return !$this->getErrorAggregator()->isRowInvalid($rowNumber);
    }

    /**
     * Check Of Increment Id Data
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     */
    protected function validateIncrementId(array $rowData, $rowNumber)
    {
        if (empty($rowData[static::COLUMN_INCREMENT_ID])) {
            $this->addRowError(static::ERROR_INCREMENT_ID_IS_EMPTY, $rowNumber);
        }
        return true;
    }

    /**
     * @param $bunchSize
     * @param $bunchRows
     * @return bool
     */
    protected function isBunchSizeExceeded($bunchSize, $bunchRows)
    {
        $isBunchSizeExceeded = $bunchSize > 0 && count($bunchRows) >= $bunchSize;
        return $isBunchSizeExceeded;
    }

    /**
     * Retrieve Next Entity Id by Auto Increment Column
     *
     * @return int
     */
    protected function getNextEntityId()
    {
        if (!$this->nextEntityId) {
            $this->nextEntityId = $this->resourceHelper->getNextAutoincrement(
                $this->getMainTable()
            );
        }
        return $this->nextEntityId++;
    }

    /**
     * Get product Table Name
     *
     * @return string
     */
    public function getProductTable()
    {
        return $this->resource->getTableName(
            $this->productTable
        );
    }

    /**
     * Get Customer Table Name
     *
     * @return string
     */
    public function getCustomerTable()
    {
        return $this->resource->getTableName(
            $this->customerTable
        );
    }

    /**
     * Get Store Table Name
     *
     * @return string
     */
    public function getStoreTable()
    {
        return $this->resource->getTableName(
            $this->storeTable
        );
    }

    /**
     * Get Order Table Name
     *
     * @return string
     */
    public function getOrderTable()
    {
        return $this->resource->getTableName(
            $this->orderTable
        );
    }

    /**
     * Get Order Status Table Name
     *
     * @return string
     */
    public function getOrderStatusTable()
    {
        return $this->resource->getTableName(
            $this->orderStatusTable
        );
    }

    /**
     * Get Order Status Label Table Name
     *
     * @return string
     */
    public function getOrderStatusLabelTable()
    {
        return $this->resource->getTableName(
            $this->orderStatusLabelTable
        );
    }

    /**
     * Get Order Status State Table Name
     *
     * @return string
     */
    public function getOrderStateTable()
    {
        return $this->resource->getTableName(
            $this->orderStateTable
        );
    }

    /**
     * Get Entity Table Name
     *
     * @return string
     */
    public function getMainTable()
    {
        return $this->resource->getTableName(
            $this->mainTable
        );
    }

    /**
     * Child entity
     *
     * @return array
     */
    protected function getChildren()
    {
        return [];
    }

    /**
     * List columns which has multiple value
     *
     * @return array
     */
    public function getMultipleValueColumns()
    {
        return $this->multipleValueColumns;
    }

    /**
     * Multiple value separator getter.
     *
     * @return string
     */
    public function getMultipleValueSeparator()
    {
        if (!empty($this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR])) {
            return $this->_parameters[Import::FIELD_FIELD_MULTIPLE_VALUE_SEPARATOR];
        }
        return Import::DEFAULT_GLOBAL_MULTI_VALUE_SEPARATOR;
    }

    /**
     * Get count of created items
     *
     * @return int
     */
    public function getCreatedItemsCount()
    {
        $count = $this->countItemsCreated;
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $count += $child->getCreatedItemsCount();
            }
        }
        return $count;
    }

    /**
     * Get count of updated items
     *
     * @return int
     */
    public function getUpdatedItemsCount()
    {
        $count = $this->countItemsUpdated;
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $count += $child->getUpdatedItemsCount();
            }
        }
        return $count;
    }

    /**
     * Get count of deleted items
     *
     * @return int
     */
    public function getDeletedItemsCount()
    {
        $count = $this->countItemsDeleted;
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $count += $child->getDeletedItemsCount();
            }
        }
        return $count;
    }

    /**
     * Return required columns of entity
     *
     * @return array
     */
    public function getRequiredValueColumns()
    {
        return $this->requiredValueColumns;
    }

    /**
     * Return exist columns in csv file
     *
     * @return array
     */
    public function getExistColumns()
    {
        return $this->existColumns;
    }

    /**
     * Return prefix code of entity
     *
     * @return string
     */
    public function getPrefixCode()
    {
        return $this->prefixCode;
    }

    /**
     * Error Aggregator Setter
     *
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @return $this
     */
    public function setErrorAggregator($errorAggregator)
    {
        $this->errorAggregator = $errorAggregator;
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->setErrorAggregator($errorAggregator);
            }
        }
        return $this;
    }

    /**
     * Set Data From Outside To Change Behavior
     *
     * @param array $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        parent::setParameters($parameters);
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->setParameters($parameters);
            }
        }
        return $this;
    }

    /**
     * Source Model Setter
     *
     * @param AbstractSource $source
     * @return $this|CoreAbstractEntity
     */
    public function setSource(AbstractSource $source)
    {
        parent::setSource($source);
        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->setSource($source);
            }
        }
        return $this;
    }

    /**
     * Get entity type code
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return static::ENTITY_TYPE_CODE;
    }
}
