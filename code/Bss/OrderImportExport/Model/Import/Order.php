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
namespace Bss\OrderImportExport\Model\Import;

use Magento\ImportExport\Model\Import;
use Magento\ImportExport\Model\Import\AbstractSource;
use Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

/**
 * Class Order
 *
 * @package Bss\OrderImportExport\Model\Import
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class Order extends Import\Entity\AbstractEntity
{
    const ENTITY_TYPE_CODE = "bss_order";

    /**
     * maximum order number per bunch
     */
    const ORDER_NUMBER_PER_BUNCH = 500;

    /**
     * error code
     */
    const ERROR_COLUMN_IS_EMPTY = 'columnIsEmpty';

    /**
     * Validation General Message Template For All Entity
     *
     * @var array
     */
    protected $generalMessageTemplates = [
        self::ERROR_COLUMN_IS_EMPTY => "Column '%s' is empty"
    ];

    /**
     * Need to log in import history
     *
     * @var bool
     */
    protected $logInHistory = true;

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
     * @var Entity\Order
     */
    protected $orderEntity;

    /**
     * @var Entity\Item
     */
    protected $itemEntity;

    /**
     * @var Entity\Address
     */
    protected $addressEntity;

    /**
     * @var Entity\Shipment
     */
    protected $shipmentEntity;

    /**
     * @var Entity\Payment
     */
    protected $paymentEntity;

    /**
     * @var Entity\Tax
     */
    protected $taxEntity;

    /**
     * @var Entity\StatusHistory
     */
    protected $statusHistoryEntity;

    /**
     * @var Entity\Invoice
     */
    protected $invoiceEntity;

    /**
     * @var Entity\Creditmemo
     */
    protected $creditmemoEntity;

    /**
     * @var Entity\Tax\Item
     */
    protected $taxItemEntity;

    /**
     * @var Entity\Payment\Transaction
     */
    protected $transactionEntity;

    /**
     * @var Entity\Shipment\Track
     */
    protected $trackEntity;

    /**
     * @var Entity\Shipment\Item
     */
    protected $shipmentItemEntity;

    /**
     * @var Entity\Shipment\Comment
     */
    protected $shipmentCommentEntity;

    /**
     * @var Entity\Invoice\Item
     */
    protected $invoiceItemEntity;

    /**
     * @var Entity\Invoice\Comment
     */
    protected $invoiceCommentEntity;

    /**
     * @var Entity\Creditmemo\Item
     */
    protected $creditmemoItemEntity;

    /**
     * @var Entity\Creditmemo\Comment
     */
    protected $creditmemoCommentEntity;

    /**
     * @var Entity\DownloadLink
     */
    protected $downloadLinkEntity;

    /**
     * @var Entity\DownloadLink\Item
     */
    protected $downloadLinkItemEntity;

    /**
     * @var \Bss\OrderImportExport\Model\ResourceModel\GridPool
     */
    protected $gridPool;

    /**
     * @var Mapping\Mapping
     */
    protected $mapping;

    /**
     * Json Serializer Instance
     *
     * @var Json
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * Order constructor.
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\ImportExport\Helper\Data $importExportData
     * @param \Magento\ImportExport\Model\ResourceModel\Import\Data $importData
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper
     * @param \Magento\Framework\Stdlib\StringUtils $string
     * @param ProcessingErrorAggregatorInterface $errorAggregator
     * @param Entity\OrderFactory $orderEntityFactory
     * @param Entity\ItemFactory $itemEntityFactory
     * @param Entity\AddressFactory $addressEntityFactory
     * @param Entity\ShipmentFactory $shipmentEntityFactory
     * @param Entity\PaymentFactory $paymentEntityFactory
     * @param Entity\TaxFactory $taxEntityFactory
     * @param Entity\StatusHistoryFactory $statusHistoryEntityFactory
     * @param Entity\InvoiceFactory $invoiceEntityFactory
     * @param Entity\CreditmemoFactory $creditmemoEntityFactory
     * @param Entity\Tax\ItemFactory $taxItemEntityFactory
     * @param Entity\Payment\TransactionFactory $transactionEntityFactory
     * @param Entity\Shipment\TrackFactory $trackEntityFactory
     * @param Entity\Shipment\ItemFactory $shipmentItemEntityFactory
     * @param Entity\Shipment\CommentFactory $shipmentCommentEntityFactory
     * @param Entity\Invoice\ItemFactory $invoiceItemEntityFactory
     * @param Entity\Invoice\CommentFactory $invoiceCommentEntityFactory
     * @param Entity\Creditmemo\ItemFactory $creditmemoItemEntityFactory
     * @param Entity\Creditmemo\CommentFactory $creditmemoCommentEntityFactory
     * @param Entity\DownloadLinkFactory $downloadLinkEntityFactory
     * @param Entity\DownloadLink\ItemFactory $downloadLinkEntityItemFactory
     * @param \Bss\OrderImportExport\Model\ResourceModel\GridPool $gridPool
     * @param Mapping\Mapping $mapping
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\ImportExport\Helper\Data $importExportData,
        \Magento\ImportExport\Model\ResourceModel\Import\Data $importData,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ImportExport\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface $errorAggregator,
        Entity\OrderFactory $orderEntityFactory,
        Entity\ItemFactory $itemEntityFactory,
        Entity\AddressFactory $addressEntityFactory,
        Entity\ShipmentFactory $shipmentEntityFactory,
        Entity\PaymentFactory $paymentEntityFactory,
        Entity\TaxFactory $taxEntityFactory,
        Entity\StatusHistoryFactory $statusHistoryEntityFactory,
        Entity\InvoiceFactory $invoiceEntityFactory,
        Entity\CreditmemoFactory $creditmemoEntityFactory,
        Entity\Tax\ItemFactory $taxItemEntityFactory,
        Entity\Payment\TransactionFactory $transactionEntityFactory,
        Entity\Shipment\TrackFactory $trackEntityFactory,
        Entity\Shipment\ItemFactory $shipmentItemEntityFactory,
        Entity\Shipment\CommentFactory $shipmentCommentEntityFactory,
        Entity\Invoice\ItemFactory $invoiceItemEntityFactory,
        Entity\Invoice\CommentFactory $invoiceCommentEntityFactory,
        Entity\Creditmemo\ItemFactory $creditmemoItemEntityFactory,
        Entity\Creditmemo\CommentFactory $creditmemoCommentEntityFactory,
        Entity\DownloadLinkFactory $downloadLinkEntityFactory,
        Entity\DownloadLink\ItemFactory $downloadLinkEntityItemFactory,
        \Bss\OrderImportExport\Model\ResourceModel\GridPool $gridPool,
        \Bss\OrderImportExport\Model\Import\Mapping\Mapping $mapping,
        \Magento\Framework\Module\ModuleListInterface $moduleList
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->importExportData = $importExportData;
        $this->resourceHelper = $resourceHelper;
        $this->string = $string;
        $this->errorAggregator = $errorAggregator;
        $this->errorMessageTemplates = array_merge(
            $this->errorMessageTemplates,
            $this->generalMessageTemplates
        );
        foreach ($this->errorMessageTemplates as $errorCode => $message) {
            $this->getErrorAggregator()->addErrorMessageTemplate($errorCode, $message);
        }
        $this->dataSourceModel = $importData;
        $this->connection = $resource->getConnection();
        $this->orderEntity = $orderEntityFactory->create();
        $this->itemEntity = $itemEntityFactory->create();
        $this->addressEntity = $addressEntityFactory->create();
        $this->shipmentEntity = $shipmentEntityFactory->create();
        $this->paymentEntity = $paymentEntityFactory->create();
        $this->taxEntity = $taxEntityFactory->create();
        $this->statusHistoryEntity = $statusHistoryEntityFactory->create();
        $this->invoiceEntity = $invoiceEntityFactory->create();
        $this->creditmemoEntity = $creditmemoEntityFactory->create();
        $this->taxItemEntity = $taxItemEntityFactory->create();
        $this->transactionEntity = $transactionEntityFactory->create();
        $this->trackEntity = $trackEntityFactory->create();
        $this->shipmentItemEntity = $shipmentItemEntityFactory->create();
        $this->shipmentCommentEntity = $shipmentCommentEntityFactory->create();
        $this->invoiceItemEntity = $invoiceItemEntityFactory->create();
        $this->invoiceCommentEntity = $invoiceCommentEntityFactory->create();
        $this->creditmemoItemEntity = $creditmemoItemEntityFactory->create();
        $this->creditmemoCommentEntity = $creditmemoCommentEntityFactory->create();
        $this->downloadLinkEntity = $downloadLinkEntityFactory->create();
        $this->downloadLinkItemEntity = $downloadLinkEntityItemFactory->create();
        $this->gridPool = $gridPool;
        $this->mapping = $mapping;
        $this->moduleList = $moduleList;
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

    /**
     * List Entity for Import Order
     *
     * @return array
     */
    protected function getEntities()
    {
        return [
            $this->orderEntity,
            Constant::PREFIX_ORDER_ITEM => $this->itemEntity,
            Constant::PREFIX_ORDER_ADDRESS => $this->addressEntity,
            Constant::PREFIX_ORDER_PAYMENT => $this->paymentEntity,
            Constant::PREFIX_ORDER_TAX => $this->taxEntity,
            Constant::PREFIX_ORDER_STATUS_HISTORY => $this->statusHistoryEntity,
            Constant::PREFIX_SHIPMENT => $this->shipmentEntity,
            Constant::PREFIX_INVOICE => $this->invoiceEntity,
            Constant::PREFIX_CREDITMEMO => $this->creditmemoEntity,
            Constant::PREFIX_ORDER_DOWNLOAD_LINK => $this->downloadLinkEntity
        ];
    }

    /**
     * List Entity for Import Order
     *
     * @return array
     */
    protected function getAllEntities()
    {
        return [
            $this->orderEntity,
            Constant::PREFIX_ORDER_ITEM => $this->itemEntity,
            Constant::PREFIX_ORDER_ADDRESS => $this->addressEntity,
            Constant::PREFIX_ORDER_PAYMENT => $this->paymentEntity,
            Constant::PREFIX_ORDER_TAX => $this->taxEntity,
            Constant::PREFIX_ORDER_STATUS_HISTORY => $this->statusHistoryEntity,
            Constant::PREFIX_SHIPMENT => $this->shipmentEntity,
            Constant::PREFIX_INVOICE => $this->invoiceEntity,
            Constant::PREFIX_CREDITMEMO => $this->creditmemoEntity,

            Constant::PREFIX_ORDER_TAX_ITEM => $this->taxItemEntity,
            Constant::PREFIX_ORDER_PAYMENT_TRANSACTION => $this->transactionEntity,
            Constant::PREFIX_SHIPMENT_TRACK => $this->trackEntity,
            Constant::PREFIX_SHIPMENT_ITEM => $this->shipmentItemEntity,
            Constant::PREFIX_SHIPMENT_COMMENT => $this->shipmentCommentEntity,
            Constant::PREFIX_INVOICE_ITEM => $this->invoiceItemEntity,
            Constant::PREFIX_INVOICE_COMMENT => $this->invoiceCommentEntity,
            Constant::PREFIX_CREDITMEMO_ITEM => $this->creditmemoItemEntity,
            Constant::PREFIX_CREDITMEMO_COMMENT => $this->creditmemoCommentEntity,

            Constant::PREFIX_ORDER_DOWNLOAD_LINK => $this->downloadLinkEntity,
            Constant::PREFIX_ORDER_DOWNLOAD_LINK_ITEM => $this->downloadLinkItemEntity
        ];
    }

    /**
     * Import data
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _importData()
    {
        /* import data */
        try {
            while ($bunch = $this->dataSourceModel->getNextBunch()) {
                $this->setCurrentBunch($bunch);
                $this->orderEntity->resetBunchOrderIds();
                if ($this->getBehavior() == Import::BEHAVIOR_APPEND) {
                    if ($this->orderEntity->importData()) {
                        $this->importEntities();

                        // Move data to grid table
                        if ($orderIds = $this->orderEntity->getBunchOrderIds()) {
                            $this->gridPool->refreshByOrderIds($orderIds);
                        }
                    }
                } elseif ($this->getBehavior() == Constant::BEHAVIOR_UPDATE) {
                    $this->orderEntity->importData();
                    $this->importEntities();

                    // Move data to grid table
                    if ($orderIds = $this->orderEntity->getBunchOrderIds()) {
                        $this->gridPool->refreshByOrderIds($orderIds);
                    }
                } elseif ($this->getBehavior() == Import::BEHAVIOR_DELETE) {
                    $this->orderEntity->importData();
                }
            }

            foreach ($this->getEntities() as $entity) {
                $this->countItemsCreated += $entity->getCreatedItemsCount();
                $this->countItemsUpdated+= $entity->getUpdatedItemsCount();
                $this->countItemsDeleted += $entity->getDeletedItemsCount();
            }
        } catch (\Exception $e) {
            $this->getErrorAggregator()->addError(
                $e->getMessage(),
                \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingError::ERROR_LEVEL_CRITICAL
            );
            return false;
        }
        $this->mapping->clearMappingSession();
        return true;
    }

    /**
     * Import children entities of order
     */
    protected function importEntities()
    {
        $orderIdsMapped = $this->orderEntity->getOrderIdsMapped();
        $baseRatesMapped = $this->orderEntity->getBaseRatesMapped();
        $taxRatesMapped = $this->orderEntity->getTaxRatesMapped();
        $this->setOrderIdsMapped($orderIdsMapped);
        $this->setBaseRatesMapped($baseRatesMapped);
        $this->setTaxRatesMapped($taxRatesMapped);

        $this->importItem();
        $itemIdsMapped = $this->itemEntity->getItemIdsMapped();
        $this->setItemIdsMapped($itemIdsMapped);

        if ($this->itemEntity->hasDownloadableItem()) {
            $this->downloadLinkEntity->importData();
        }

        $this->importAddress();
        $addressIdsMapped = $this->addressEntity->getAddressIdsMapped();
        $this->setAddressIdsMapped($addressIdsMapped);

        $this->importTax();
        $this->importStatusHistory();
        $this->importPayment();

        $this->importShipment();
        $this->importInvoice();
        $this->importCreditmemo();
    }

    /**
     * @param $bunch
     */
    protected function setCurrentBunch($bunch)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setCurrentBunch($bunch);
        }
    }

    /**
     * Import Order Item Data
     *
     */
    public function importItem()
    {
        $this->itemEntity->importData();
    }

    /**
     * Import Address Data
     *
     * @return void
     */
    protected function importAddress()
    {
        $this->addressEntity->importData();
    }

    /**
     * Import Shipment Data
     *
     * @return void
     */
    protected function importShipment()
    {
        $this->shipmentEntity->importData();
    }

    /**
     * Import Payment Data
     *
     * @return void
     */
    protected function importPayment()
    {
        $this->paymentEntity->importData();
    }

    /**
     * Import Tax Data
     *
     * @return void
     */
    protected function importTax()
    {
        $this->taxEntity->importData();
    }

    /**
     * Import Status Sistory Data
     *
     * @return void
     */
    protected function importStatusHistory()
    {
        $this->statusHistoryEntity->importData();
    }

    /**
     * Import Invoice Data
     *
     * @return void
     */
    protected function importInvoice()
    {
        $this->invoiceEntity->importData();
    }

    /**
     * Import Creditmemo Data
     *
     * @return void
     */
    protected function importCreditmemo()
    {
        $this->creditmemoEntity->importData();
    }

    /**
     * @return ProcessingErrorAggregatorInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateData()
    {
        $this->mapping->clearMappingSession();
        if (!$this->_dataValidated) {
            $this->getErrorAggregator()->clear();
            foreach ($this->getEntities() as $entity) {
                $entity->validateData();
            }

            if (!$this->getErrorAggregator()->getErrorsCount()) {
                $this->prepareDefaultMapping();
                $this->saveValidatedBunches();
                $this->_dataValidated = true;
            } else {
                $this->validateAllRow();
            }
        }
        return $this->getErrorAggregator();
    }

    /**
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function validateAllRow()
    {
        $source = $this->_getSource();
        $source->rewind();

        while ($source->valid()) {
            $rowData = $source->current();
            $this->validateRow($rowData, $source->key());
            $source->next();
        }

        $orderToValidates = $this->addressEntity->getOrderToValidate();
        $errorOrders = array_filter($orderToValidates, function ($item) {
            return empty($item['billing']) || (empty($item['is_virtual']) && empty($item['shipping']));
        });
        foreach ($errorOrders as $orderNumber => $arr) {
            if (empty($arr['billing'])) {
                $this->addRowError(
                    __("The billing address of order number `%1` is missing", $orderNumber),
                    isset($arr['index']) ? $arr['index'] : 0
                );
            }
            if (empty($arr['is_virtual']) && empty($arr['shipping'])) {
                $this->addRowError(
                    __("The shipping address of order number `%1` is missing", $orderNumber),
                    isset($arr['index']) ? $arr['index'] : 0
                );
            }
        }
        $this->addressEntity->resetOrderToValidate();

        return $this;
    }

    /**
     * Validate data row.
     *
     * @param array $rowData
     * @param int $rowNumber
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validateRow(array $rowData, $rowNumber)
    {
        $orderRowData = false;
        $emptyEntity = [];
        $validEntity = [];
        $invalidEntity = [];
        $hasTax = false;

        if ((!empty($rowData['tax_amount']) && $rowData['tax_amount'] > 0) ||
            (!empty($rowData['base_tax_amount']) && $rowData['base_tax_amount'] > 0)
        ) {
            $hasTax = true;
        }

        if ($this->getBehavior() == Import::BEHAVIOR_DELETE) {
            $orderRowData = $entityData = $this->orderEntity->extractRowData($rowData);
            if ($entityData && array_filter($entityData)) {
                if (!$this->orderEntity->validateRow($entityData, $rowNumber)) {
                    $invalidEntity[] = '';
                } else {
                    $validEntity[] = '';
                }
            } else {
                $emptyEntity[] = '';
            }
        } else {
            foreach ($this->getAllEntities() as $prefix => $entity) {
                $entityData = $entity->extractRowData($rowData, $rowNumber);
                if (!$prefix) {
                    $orderRowData = $entityData;
                }

                if ($entityData && array_filter($entityData)) {

                    if (!$entity->validateRow($entityData, $rowNumber)) {
                        $invalidEntity[] = $prefix;
                    } else {
                        $validEntity[] = $prefix;
                    }
                } else {
                    $emptyEntity[] = $prefix;
                }
            }
        }

        $missEntities = [];
        if ($orderRowData && !empty($emptyEntity)) {
            if ($this->getBehavior() == Import::BEHAVIOR_APPEND) {
                $requiredEntities = [
                    Constant::PREFIX_ORDER_ITEM,
                    Constant::PREFIX_ORDER_PAYMENT,
                    Constant::PREFIX_ORDER_ADDRESS
                ];
                if ($hasTax) {
                    $requiredEntities = array_merge($requiredEntities, [
                        Constant::PREFIX_ORDER_TAX,
                        Constant::PREFIX_ORDER_TAX_ITEM
                    ]);
                }
                if ($missEntities = array_intersect($emptyEntity, $requiredEntities)) {
                    $this->processErrorForMissEntities($missEntities, $rowNumber);
                }
            }
        }

        if (!empty($invalidEntity) || !empty($missEntities)) {
            return false;
        }

        if (empty($validEntity)) {
            $this->addRowError(__("Empty data"), $rowNumber);
            return false;
        }

        return true;
    }

    /**
     * @param $entities
     * @param $rowNumber
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function processErrorForMissEntities($entities, $rowNumber)
    {
        foreach ($entities as $entityCode) {
            $entityObj = false;
            switch ($entityCode) {
                case Constant::PREFIX_ORDER_ITEM:
                    $entityObj = $this->itemEntity;
                    break;
                case Constant::PREFIX_ORDER_PAYMENT:
                    $entityObj = $this->paymentEntity;
                    break;
                case Constant::PREFIX_ORDER_ADDRESS:
                    $entityObj = $this->addressEntity;
                    break;
                case Constant::PREFIX_ORDER_TAX:
                    $entityObj = $this->taxEntity;
                    break;
                case Constant::PREFIX_ORDER_TAX_ITEM:
                    $entityObj = $this->taxItemEntity;
                    break;
            }

            if ($entityObj) {
                $existColumns = $entityObj->getExistColumns();
                $notExistColumns = [];
                foreach ($entityObj->getRequiredValueColumns() as $column) {
                    if (in_array($column, $existColumns)) {
                        $this->addRowError(
                            static::ERROR_COLUMN_IS_EMPTY,
                            $rowNumber,
                            $entityObj->getPrefixCode().':'.$column
                        );
                    } else {
                        $notExistColumns[] = $entityObj->getPrefixCode().':'.$column;
                    }
                }

                if ($notExistColumns) {
                    $this->addErrors(self::ERROR_CODE_COLUMN_NOT_FOUND, $notExistColumns);
                }
            }
        }
    }

    /**
     * Import behavior getter.
     *
     * @return string
     */
    public function getBehavior()
    {
        if (!isset(
            $this->_parameters['behavior']
        ) ||
            $this->_parameters['behavior'] != Import::BEHAVIOR_APPEND &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_ADD_UPDATE &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_REPLACE &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_CUSTOM &&
            $this->_parameters['behavior'] != Import::BEHAVIOR_DELETE &&
            $this->_parameters['behavior'] != Constant::BEHAVIOR_UPDATE
        ) {
            return Import::getDefaultBehavior();
        }
        return $this->_parameters['behavior'];
    }

    /**
     * Prepare default mapping to session
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function prepareDefaultMapping()
    {
        $source = $this->_getSource();
        $source->rewind();
        $processedRowsCount = 0;

        while ($source->valid()) {
            try {
                $rowData = $source->current();
            } catch (\InvalidArgumentException $e) {
                $this->addRowError($e->getMessage(), $processedRowsCount);
                $processedRowsCount++;
                $source->next();
                continue;
            }
            $processedRowsCount++;
            $this->mapping->prepareMappingData($rowData, true, $this->getBehavior());
            $source->next();
        }
        $this->mapping->map($this->getBehavior());

        foreach ($this->getEntities() as $entity) {
            $entity->initDefaultMapping();
        }

        return $this;
    }

    /**
     * Save Validated Bunches
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function saveValidatedBunches()
    {
        $source = $this->_getSource();
        $currentDataSize = 0;
        $bunchRows = [];
        $startNewBunch = false;
        $nextRowBackup = [];
        $maxDataSize = $this->resourceHelper->getMaxDataSize();
        $bunchSize = self::ORDER_NUMBER_PER_BUNCH;
        $orderCount = 0;

        $source->rewind();
        $this->dataSourceModel->cleanBunches();

        while ($source->valid() || $bunchRows) {
            if ($startNewBunch || !$source->valid()) {
                $this->dataSourceModel->saveBunch($this->getEntityTypeCode(), $this->getBehavior(), $bunchRows);

                $bunchRows = $nextRowBackup;
                $currentDataSize = strlen($this->getSerializer()->serialize($bunchRows));
                $startNewBunch = false;
                $nextRowBackup = [];
                $orderCount = 0;

                $orderToValidates = $this->addressEntity->getOrderToValidate();
                $errorOrders = array_filter($orderToValidates, function ($item) {
                    return empty($item['billing']) || (empty($item['is_virtual']) && empty($item['shipping']));
                });
                foreach ($errorOrders as $orderNumber => $arr) {
                    if (empty($arr['billing'])) {
                        $this->addRowError(
                            __("The billing address of order number `%1` is missing", $orderNumber),
                            $arr['index']
                        );
                    }
                    if (empty($arr['is_virtual']) && empty($arr['shipping'])) {
                        $this->addRowError(
                            __("The shipping address of order number `%1` is missing", $orderNumber),
                            $arr['index']
                        );
                    }
                }
                $this->addressEntity->resetOrderToValidate();
            }
            if ($source->valid()) {
                try {
                    $rowData = $source->current();
                } catch (\InvalidArgumentException $e) {
                    $this->addRowError($e->getMessage(), $this->_processedRowsCount);
                    $this->_processedRowsCount++;
                    $source->next();
                    continue;
                }

                $this->_processedRowsCount++;

                if ($this->validateRow($rowData, $source->key())) {
                    if (!empty($rowData['increment_id'])) {
                        $orderCount ++;
                    }
                    // add row to bunch for save
                    $rowData = $this->_prepareRowForDb($rowData);
                    $rowSize = strlen($this->jsonHelper->jsonEncode($rowData));

                    $isBunchSizeExceeded = $this->isBunchSizeExceeded($bunchSize, $orderCount);

                    if ($currentDataSize + $rowSize >= $maxDataSize || $isBunchSizeExceeded) {
                        $startNewBunch = true;
                        $nextRowBackup = [$source->key() => $rowData];
                    } else {
                        $bunchRows[$source->key()] = $rowData;
                        $currentDataSize += $rowSize;
                    }
                }
                $source->next();
            }
        }
        return $this;
    }

    /**
     * @param $bunchSize
     * @param $orderCount
     * @return bool
     */
    protected function isBunchSizeExceeded($bunchSize, $orderCount)
    {
        $isBunchSizeExceeded = $bunchSize > 0 && $orderCount >= $bunchSize;
        return $isBunchSizeExceeded;
    }

    /**
     * Get Serializer instance
     *
     * Workaround. Only way to implement dependency and not to break inherited child classes
     *
     * @return Json
     * @deprecated 100.2.0
     */
    private function getSerializer()
    {
        if (null === $this->serializer) {
            $this->serializer = ObjectManager::getInstance()->get(Json::class);
        }
        return $this->serializer;
    }

    /**
     * Set Order Ids Map
     *
     * @param array $orderIdsMapped
     */
    protected function setOrderIdsMapped(array $orderIdsMapped)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setOrderIdsMapped($orderIdsMapped);
        }
    }

    /**
     * Set Base Rate Map
     *
     * @param array $baseRatesMapped
     */
    protected function setBaseRatesMapped(array $baseRatesMapped)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setBaseRatesMapped($baseRatesMapped);
        }
    }

    /**
     * Set Tax Rate Map
     *
     * @param array $taxRatesMapped
     */
    protected function setTaxRatesMapped(array $taxRatesMapped)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setTaxRatesMapped($taxRatesMapped);
        }
    }

    /**
     * Set Order Item Ids Map
     *
     * @param array $itemIdsMapped
     */
    protected function setItemIdsMapped(array $itemIdsMapped)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setItemIdsMapped($itemIdsMapped);
        }
    }

    /**
     * Set Address Ids Map
     *
     * @param array $addressIdsMapped
     */
    protected function setAddressIdsMapped(array $addressIdsMapped)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setAddressIdsMapped($addressIdsMapped);
        }
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
        foreach ($this->getEntities() as $entity) {
            $entity->setErrorAggregator($errorAggregator);
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
        foreach ($this->getEntities() as $entity) {
            $entity->setParameters($parameters);
        }
        return $this;
    }

    /**
     * Source Model Setter
     *
     * @param AbstractSource $source
     * @return \Magento\ImportExport\Model\Import\AbstractEntity
     */
    public function setSource(AbstractSource $source)
    {
        foreach ($this->getEntities() as $entity) {
            $entity->setSource($source);
        }
        return parent::setSource($source);
    }

    /**
     * Acl Resource
     *
     * @return string
     */
    public function getAclResource()
    {
        return "Bss_OrderImportExport::bss_order_import";
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $moduleInfo = $this->moduleList->getOne("Bss_OrderImportExport");
        return $moduleInfo['setup_version'];
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return [
            "image_import" => false
        ];
    }
}
