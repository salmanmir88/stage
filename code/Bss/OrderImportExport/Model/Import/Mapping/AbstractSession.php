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
namespace Bss\OrderImportExport\Model\Import\Mapping;

use Magento\Framework\Session\Config\ConfigInterface;

/**
 * Class AbstractSession
 *
 * @package Bss\OrderImportExport\Model\Import\Mapping
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
abstract class AbstractSession extends \Magento\Framework\Session\SessionManager
{
    /**
     * Default identify column
     */
    const COLUMN_IDENTIFY = 'increment_id';

    /**
     * Order increment id column
     */
    const COLUMN_INCREMENT_ID = 'increment_id';

    /**
     * Primary column
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Prefix identify key for session
     */
    const MAPPING_KEY = 'bss_map';

    /**
     * Prefix code for csv column
     *
     * @var string
     */
    protected $prefixCode = "";

    /**
     * Entity table name
     *
     * @var string
     */
    protected $mainTable;

    /**
     * Current increment id for loop
     *
     * @var bool|int
     */
    protected $currentIncrementId = false;

    /**
     * @var array
     */
    protected $conditionValues = [];

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceModel;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    protected $connection;

    /**
     * Order Ids Map
     *
     * @var array
     */
    protected $orderIdsMapped;

    /**
     * Order Item Ids Map
     *
     * @var array
     */
    protected $orderItemIdsMapped;

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
     * Order Ids Map
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
     * AbstractSession constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Session\SessionStartChecker $sessionStartChecker
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Session\SessionStartChecker $sessionStartChecker,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        parent::__construct(
            $request,
            $sidResolver,
            $sessionConfig,
            $saveHandler,
            $validator,
            $storage,
            $cookieManager,
            $cookieMetadataFactory,
            $appState,
            $sessionStartChecker
        );
        $this->resourceModel = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @return mixed
     */
    protected function getMainTable()
    {
        return $this->resourceModel->getTableName($this->mainTable);
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        return [];
    }

    /**
     * @param $rowData
     * @param bool $hasPrefix
     */
    abstract public function prepareMappingData($rowData, $hasPrefix);

    /**
     * @param $rowData
     */
    protected function extractRow($rowData)
    {
        if (!empty($rowData[static::COLUMN_INCREMENT_ID])) {
            $this->currentIncrementId = $rowData[static::COLUMN_INCREMENT_ID];
        }
    }

    /**
     * Map all entity id from database after collect all identify from csv
     */
    abstract public function map();

    /**
     * Store mapped array to session
     *
     * @param $mappedData
     */
    protected function setMapped($mappedData)
    {
        $this->storage->setData(static::MAPPING_KEY, $mappedData);
    }

    /**
     * Add a mapped to session
     *
     * @param $rowData
     * @param $dbValue
     */
    public function addMapped($rowData, $dbValue)
    {
        $mappedArr = $this->getMapped();
        $mappedArr[$rowData[static::COLUMN_IDENTIFY]] = $dbValue;
        $this->setMapped($mappedArr);
    }

    /**
     * @param bool|string $mapKey
     * @return mixed
     */
    public function getMapped($mapKey = false)
    {
        if ($mapKey) {
            return isset($this->getData(static::MAPPING_KEY)[$mapKey])
                ? $this->getData(static::MAPPING_KEY)[$mapKey]
                : [];
        }
        return $this->getData(static::MAPPING_KEY);
    }

    /**
     * Clear mapping from session
     */
    public function clearMapping()
    {
        $this->conditionValues = [];
        $this->setMapped([]);
    }

    /**
     * @param $orderIds
     */
    public function setOrderIdsMapped($orderIds)
    {
        $this->orderIdsMapped = $orderIds;
    }

    /**
     * @return array
     */
    public function getOrderIdsMapped()
    {
        return $this->orderIdsMapped ?: [];
    }

    /**
     * @param $orderItemIds
     */
    public function setOrderItemIdsMapped($orderItemIds)
    {
        $this->orderItemIdsMapped = $orderItemIds;
    }

    /**
     * @return array
     */
    public function getOrderItemIdsMapped()
    {
        return $this->orderItemIdsMapped ?: [];
    }

    /**
     * @param $taxIds
     */
    public function setTaxIdsMapped($taxIds)
    {
        $this->taxIdsMapped = $taxIds;
    }

    /**
     * @return array
     */
    public function getTaxIdsMapped()
    {
        return $this->taxIdsMapped ?: [];
    }

    /**
     * @param $paymentIds
     */
    public function setPaymentIdsMapped($paymentIds)
    {
        $this->paymentIdsMapped = $paymentIds;
    }

    /**
     * @return array
     */
    public function getPaymentIdsMapped()
    {
        return $this->paymentIdsMapped ?: [];
    }

    /**
     * @param $mappedIds
     */
    public function setShipmentIdsMapped($mappedIds)
    {
        $this->shipmentIdsMapped = $mappedIds;
    }

    /**
     * @return array
     */
    public function getShipmentIdsMapped()
    {
        return $this->shipmentIdsMapped ?: [];
    }

    /**
     * @param $mappedIds
     */
    public function setInvoiceIdsMapped($mappedIds)
    {
        $this->invoiceIdsMapped = $mappedIds;
    }

    /**
     * @return array
     */
    public function getInvoiceIdsMapped()
    {
        return $this->invoiceIdsMapped ?: [];
    }

    /**
     * @param $mappedIds
     */
    public function setCreditmemoIdsMapped($mappedIds)
    {
        $this->creditmemoIdsMapped = $mappedIds;
    }

    /**
     * @return array
     */
    public function getCreditmemoIdsMapped()
    {
        return $this->creditmemoIdsMapped ?: [];
    }
}
