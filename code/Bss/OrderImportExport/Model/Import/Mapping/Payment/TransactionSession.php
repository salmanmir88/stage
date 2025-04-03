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
namespace Bss\OrderImportExport\Model\Import\Mapping\Payment;

use Bss\OrderImportExport\Model\Import\Constant;
use Bss\OrderImportExport\Model\Import\Mapping\AbstractSession;

/**
 * Class TransactionSession
 *
 * @package Bss\OrderImportExport\Model\Import\Mapping\Payment
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class TransactionSession extends AbstractSession
{
    const COLUMN_ENTITY_ID = 'transaction_id';
    const COLUMN_ORDER_ID = 'order_id';

    protected $prefixCode = Constant::PREFIX_ORDER_PAYMENT_TRANSACTION;
    protected $mainTable = 'sales_payment_transction';
    const MAPPING_KEY = 'bss_map_payment_trasaction';

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
