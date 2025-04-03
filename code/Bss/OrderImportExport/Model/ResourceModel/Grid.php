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
namespace Bss\OrderImportExport\Model\ResourceModel;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Sales\Model\ResourceModel\Provider\NotSyncedDataProviderInterface;

class Grid extends \Magento\Sales\Model\ResourceModel\Grid
{
    /**
     * Magento product edition
     */
    const COMMUNITY_EDITION_NAME  = 'Community';

    /**
     * Grid constructor.
     * @param Context $context
     * @param \Magento\Framework\App\ProductMetadataInterface $productMetadata
     * @param string $mainTableName
     * @param string $gridTableName
     * @param string $orderIdField
     * @param array $joins
     * @param array $columns
     * @param string $connectionName
     * @param NotSyncedDataProviderInterface $notSyncedDataProvider
     */
    public function __construct(
        Context $context,
        \Magento\Framework\App\ProductMetadataInterface $productMetadata,
        $mainTableName,
        $gridTableName,
        $orderIdField,
        array $joins = [],
        array $columns = [],
        $connectionName = null,
        NotSyncedDataProviderInterface $notSyncedDataProvider = null
    ) {
        if ($productMetadata->getEdition() == self::COMMUNITY_EDITION_NAME) {
            if (isset($columns['refunded_to_store_credit'])) {
                unset($columns['refunded_to_store_credit']);
            }
        }
        parent::__construct(
            $context,
            $mainTableName,
            $gridTableName,
            $orderIdField,
            $joins,
            $columns,
            $connectionName,
            $notSyncedDataProvider
        );
    }

    /**
     * Adds new orders to the grid.
     *
     * Only orders that correspond to $value and $field parameters will be added.
     *
     * @param int|array $values
     * @param null|string $field
     * @return \Zend_Db_Statement_Interface
     */
    public function refreshMultiple($values, $field = null)
    {
        $select = $this->getGridOriginSelect()
            ->where(($field ?: $this->mainTableName . '.entity_id') . ' in (?)', $values);
        $sql = $this->getConnection()
            ->insertFromSelect(
                $select,
                $this->getTable($this->gridTableName),
                array_keys($this->columns),
                AdapterInterface::INSERT_ON_DUPLICATE
            );

        $this->addCommitCallback(function () use ($sql) {
            $this->getConnection()->query($sql);
        });

        // need for backward compatibility
        return $this->getConnection()->query($sql);
    }
}
