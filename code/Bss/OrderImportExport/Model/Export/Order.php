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
namespace Bss\OrderImportExport\Model\Export;

use Magento\Sales\Model\ResourceModel\Order\Collection as OrderCollection;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\ImportExport\Model\Export;
use Bss\OrderImportExport\Block\Adminhtml\Export\Filter\Form as FilterForm;
use Magento\ImportExport\Model\Export\Adapter\AbstractAdapter;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;

/**
 * Class Order
 *
 * @package Bss\OrderImportExport\Model\Export
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Order extends \Magento\ImportExport\Model\Export\AbstractEntity
{
    /**
     * @var array
     */
    protected $exportItemData;

    /**
     * @var OrderCollection
     */
    protected $orderCollection;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Status\Collection
     */
    protected $statusCollection;

    /**
     * Resource Model
     *
     * @var ResourceConnection
     */
    protected $resourceModel;

    /**
     * DB connection
     *
     * @var AdapterInterface
     */
    protected $connection;

    /**
     * status label by code
     *
     * @var null|array
     */
    protected $statusArray;

    /**
     * @var string
     */
    protected $prefixCode = '';

    /**
     * Table name for entity
     *
     * @var string
     */
    protected $mainTable = 'sales_order';

    /**
     * @var Entity\Item
     */
    protected $itemEntity;

    /**
     * @var Entity\Tax
     */
    protected $taxEntity;

    /**
     * @var Entity\Payment
     */
    protected $paymentEntity;

    /**
     * @var Entity\Address
     */
    protected $addressEntity;

    /**
     * @var Entity\StatusHistory
     */
    protected $statusHistoryEntity;

    /**
     * @var Entity\Shipment
     */
    protected $shipmentEntity;

    /**
     * @var Entity\Invoice
     */
    protected $invoiceEntity;

    /**
     * @var Entity\Creditmemo
     */
    protected $creditmemoEntity;

    /**
     * @var Entity\DownloadLink
     */
    protected $downloadLinkEntity;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $moduleList;

    /**
     * Order constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param Export\Factory $collectionFactory
     * @param \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory
     * @param OrderCollection $orderCollection
     * @param \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory
     * @param OrderExportData $orderExportData
     * @param ResourceConnection $resource
     * @param Entity\ItemFactory $itemEntityFactory
     * @param Entity\TaxFactory $taxEntityFactory
     * @param Entity\PaymentFactory $paymentEntityFactory
     * @param Entity\AddressFactory $addressEntityFactory
     * @param Entity\StatusHistoryFactory $statusHistoryEntityFactory
     * @param Entity\ShipmentFactory $shipmentEntityFactory
     * @param Entity\InvoiceFactory $invoiceEntityFactory
     * @param Entity\CreditmemoFactory $creditmemoEntityFactory
     * @param Entity\DownloadLinkFactory $downloadLinkEntityFactory
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ImportExport\Model\Export\Factory $collectionFactory,
        \Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory $resourceColFactory,
        OrderCollection $orderCollection,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        OrderExportData $orderExportData,
        ResourceConnection $resource,
        Entity\ItemFactory $itemEntityFactory,
        Entity\TaxFactory $taxEntityFactory,
        Entity\PaymentFactory $paymentEntityFactory,
        Entity\AddressFactory $addressEntityFactory,
        Entity\StatusHistoryFactory $statusHistoryEntityFactory,
        Entity\ShipmentFactory $shipmentEntityFactory,
        Entity\InvoiceFactory $invoiceEntityFactory,
        Entity\CreditmemoFactory $creditmemoEntityFactory,
        Entity\DownloadLinkFactory $downloadLinkEntityFactory,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        array $data = []
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $data);
        $this->orderCollection = $orderCollection;
        $this->statusCollection = $statusCollectionFactory->create();
        $this->exportItemData = $orderExportData;
        $this->resourceModel = $resource;
        $this->connection = $resource->getConnection();
        $this->itemEntity = $itemEntityFactory->create();
        $this->taxEntity = $taxEntityFactory->create();
        $this->paymentEntity = $paymentEntityFactory->create();
        $this->addressEntity = $addressEntityFactory->create();
        $this->statusHistoryEntity = $statusHistoryEntityFactory->create();
        $this->shipmentEntity = $shipmentEntityFactory->create();
        $this->invoiceEntity = $invoiceEntityFactory->create();
        $this->creditmemoEntity = $creditmemoEntityFactory->create();
        $this->downloadLinkEntity = $downloadLinkEntityFactory->create();
        $this->moduleList = $moduleList;
    }

    /**
     * Export process.
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function export()
    {
        $this->prepareEntityCollection($this->_getEntityCollection());
        $writer = $this->getWriter();

        $this->getWriter()->setHeaderCols($this->_getHeaderColumns());

        $this->_exportCollectionByPages($this->_getEntityCollection());

        $content = $writer->getContents();
        if (preg_match('/(\")/i', $content)) {
            $content = str_replace('\"', '\""', $content);
        }
        return $content;
    }

    /**
     * Apply filter to collection and add not skipped attributes to select
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function prepareEntityCollection(AbstractCollection $collection)
    {
        $this->filterEntityCollection($collection);
        return $collection;
    }

    /**
     * Apply filter to collection
     *
     * @param AbstractCollection $collection
     * @return AbstractCollection
     */
    protected function filterEntityCollection(AbstractCollection $collection)
    {
        if (!isset(
            $this->_parameters[Export::FILTER_ELEMENT_GROUP]
        ) || !is_array(
            $this->_parameters[Export::FILTER_ELEMENT_GROUP]
        )
        ) {
            $exportFilter = [];
        } else {
            $exportFilter = $this->_parameters[Export::FILTER_ELEMENT_GROUP];
        }

        if (!empty($exportFilter['from'])) {
            $collection->addFieldToFilter("created_at", ["from" => $exportFilter['from']]);
        }

        if (!empty($exportFilter['to'])) {
            $collection->addFieldToFilter("created_at", ["to" => $exportFilter['to']]);
        }

        return $collection;
    }

    /**
     * List of Related Entities need to export
     *
     * @return array
     */
    protected function getEntities()
    {
        return [
            $this->itemEntity,
            $this->taxEntity,
            $this->paymentEntity,
            $this->addressEntity,
            $this->statusHistoryEntity,
            $this->shipmentEntity,
            $this->invoiceEntity,
            $this->creditmemoEntity,
            $this->downloadLinkEntity
        ];
    }

    /**
     * Export one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function exportItem($item)
    {
        $this->resetItemData();
        $itemData = $this->extractData($item);

        foreach ($itemData as $row) {
            $this->getWriter()->writeRow($row);
        }
    }

    /**
     * Reset A Order Data
     */
    protected function resetItemData()
    {
        $this->exportItemData->reset();
    }

    /**
     * Extract one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return array
     */
    public function extractData($item)
    {
        $itemData = $this->initExtractData($item);
        $itemData['status_label'] = isset($itemData['status']) ? $this->getStatusLabel($itemData['status']) : '';
        $this->exportItemData->addRow($itemData, 0);

        foreach ($this->getEntities() as $entity) {
            $entity->setCurrentOrderExport($this->exportItemData);
            $entity->extractData($item->getId());
        }

        return $this->exportItemData->getData();
    }

    /**
     * Init Extract one item
     *
     * @param \Magento\Framework\Model\AbstractModel $item
     * @return array
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    protected function initExtractData($item)
    {
        $rowData = $item->toArray();
        if (!empty($this->validColumns)) {
            foreach ($rowData as $key => $value) {
                if (!in_array($key, $this->validColumns)) {
                    unset($rowData[$key]);
                }
            }
        }

        return $rowData;
    }

    /**
     * @param $status
     * @return mixed|string
     */
    protected function getStatusLabel($status)
    {
        if (null === $this->statusArray) {
            $this->statusArray = [];
            foreach ($this->statusCollection as $item) {
                $this->statusArray[$item->getStatus()] = $item->getLabel();
            }
        }

        return isset($this->statusArray[$status]) ? $this->statusArray[$status] : '';
    }

    /**
     * Get entity collection
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    protected function _getEntityCollection()
    {
        return $this->orderCollection;
    }

    /**
     * Set parameters
     *
     * @param string[] $parameters
     * @return $this
     */
    public function setParameters(array $parameters)
    {
        $this->_parameters = $parameters;

        foreach ($this->getEntities() as $entity) {
            $entity->setParameters($parameters);
        }

        return $this;
    }

    /**
     * Writer model setter
     *
     * @param AbstractAdapter $writer
     * @return $this
     */
    public function setWriter(AbstractAdapter $writer)
    {
        $this->_writer = $writer;

        foreach ($this->getEntities() as $entity) {
            $entity->setWriter($writer);
        }

        return $this;
    }

    /**
     * Retrieve All Entity Table Columns
     *
     * @return array
     */
    public function getMainTableFields()
    {
        return array_keys($this->connection->describeTable(
            $this->resourceModel->getTableName($this->mainTable)
        ));
    }

    /**
     * Return header columns for order
     *
     * @return array
     */
    public function getMainHeaderColumns()
    {
        $mainTableColumn = $this->getMainTableFields();
        $mainTableColumn = array_merge($mainTableColumn, ['status_label']);
        return $mainTableColumn;
    }

    /**
     * Get header columns
     *
     * @return array
     */
    protected function _getHeaderColumns()
    {
        $headerColumns = $this->getMainHeaderColumns();
        foreach ($this->getEntities() as $entity) {
            // phpcs:disable Magento2.Performance.ForeachArrayMerge
            $headerColumns = array_merge($headerColumns, $entity->_getHeaderColumns());
        }

        return $headerColumns;
    }

    /**
     * Entity type code getter
     *
     * @return string
     */
    public function getEntityTypeCode()
    {
        return 'bss_order';
    }

    /**
     * Acl Resource. This function call from Bss_ImportExportCore
     *
     * @return string
     */
    public function getAclResource()
    {
        return "Bss_OrderImportExport::bss_order_export";
    }

    /**
     * @return string
     */
    public function getFilterFormBlock()
    {
        return FilterForm::class;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        $moduleInfo = $this->moduleList->getOne("Bss_OrderImportExport");
        return $moduleInfo['setup_version'];
    }
}
