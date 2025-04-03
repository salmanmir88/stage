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

use Bss\OrderImportExport\Model\Import\Constant;
use Bss\OrderImportExport\Model\Import\Mapping\Tax;

/**
 * Class TaxSession
 *
 * @package Bss\OrderImportExport\Model\Import\Mapping
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class TaxSession extends AbstractSession
{
    const COLUMN_ENTITY_ID = 'tax_id';
    const COLUMN_ORDER_ID = 'order_id';

    protected $prefixCode = Constant::PREFIX_ORDER_TAX;
    protected $mainTable = 'sales_order_tax';
    const MAPPING_KEY = 'bss_map_tax';

    /**
     * @var Tax\ItemSession
     */
    protected $itemSession;

    /**
     * TaxSession constructor.
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\Session\SidResolverInterface $sidResolver
     * @param \Magento\Framework\Session\Config\ConfigInterface $sessionConfig
     * @param \Magento\Framework\Session\SaveHandlerInterface $saveHandler
     * @param \Magento\Framework\Session\ValidatorInterface $validator
     * @param \Magento\Framework\Session\StorageInterface $storage
     * @param \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager
     * @param \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Session\SessionStartChecker $sessionStartChecker
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param Tax\ItemSession $itemSession
     * @throws \Magento\Framework\Exception\SessionException
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SidResolverInterface $sidResolver,
        \Magento\Framework\Session\Config\ConfigInterface $sessionConfig,
        \Magento\Framework\Session\SaveHandlerInterface $saveHandler,
        \Magento\Framework\Session\ValidatorInterface $validator,
        \Magento\Framework\Session\StorageInterface $storage,
        \Magento\Framework\Stdlib\CookieManagerInterface $cookieManager,
        \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory $cookieMetadataFactory,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Session\SessionStartChecker $sessionStartChecker,
        \Magento\Framework\App\ResourceConnection $resource,
        \Bss\OrderImportExport\Model\Import\Mapping\Tax\ItemSession $itemSession
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
            $sessionStartChecker,
            $resource
        );
        $this->itemSession = $itemSession;
    }

    /**
     * @return array
     */
    protected function getChildren()
    {
        return [
            $this->itemSession
        ];
    }

    /**
     * @param $rowData
     * @param bool $hasPrefix
     */
    public function prepareMappingData($rowData, $hasPrefix)
    {
        parent::extractRow($rowData);
        if ($hasPrefix && $this->prefixCode) {
            $key = $this->prefixCode . ":" . static::COLUMN_ENTITY_ID;
        } else {
            $key = static::COLUMN_ENTITY_ID;
        }
        if (!empty($rowData[$key])) {
            $this->conditionValues[] = $rowData[$key];
        }
    }

    /**
     * Map all entity id from database after collect all identify from csv
     */
    public function map()
    {
        $mappedArray = [];
        if ($this->getMainTable() && $this->conditionValues) {
            /** @var $select \Magento\Framework\DB\Select */
            $select = $this->connection->select();
            $select->from($this->getMainTable(), [static::COLUMN_ENTITY_ID])
                ->where(
                    static::COLUMN_ENTITY_ID ." IN (?)",
                    $this->conditionValues
                );

            $result = $this->connection->query($select);
            while ($row = $result->fetch()) {
                $mappedArray[$row[static::COLUMN_ENTITY_ID]] = $row[static::COLUMN_ENTITY_ID];
            }
        }
        $this->setMapped($mappedArray);

        if ($this->getChildren()) {
            foreach ($this->getChildren() as $child) {
                $child->map();
            }
        }
    }
}
