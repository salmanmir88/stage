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
namespace Bss\OrderImportExport\Helper;

/**
 * Class Sequence
 *
 * @package Bss\OrderImportExport\Helper
 */
class Sequence
{
    const TYPE_ORDER = 'order';
    const TYPE_INVOICE = 'invoice';
    const TYPE_CREDITMEMO = 'creditmemo';
    const TYPE_SHIPMENT = 'shipment';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var \Magento\Framework\DB\Adapter\AdapterInterface
     */
    private $connection;

    /**
     * @var string
     */
    protected $profileTableName = 'sales_sequence_profile';

    /**
     * @var string
     */
    protected $metaTableName = 'sales_sequence_meta';

    /**
     * @var null|array
     */
    private $prefixs = null;

    /**
     * @var null|array
     */
    private $suffixs = null;

    /**
     * @var null|array
     */
    private $metas = null;

    /**
     * Import constructor.
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->resource = $resource;
        $this->connection = $resource->getConnection();
    }

    /**
     * @param $incrementId
     * @param $type
     * @param $storeId
     * @return bool
     * @throws \Zend_Db_Statement_Exception
     */
    public function isMagentoIncrementId($incrementId, $type, $storeId)
    {
        $regex = $this->getRegex($type, $storeId);
        if ($regex && preg_match($regex, $incrementId)) {
            return true;
        }

        return false;
    }

    /**
     * @param $type
     * @param $storeId
     * @return bool|string
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getRegex($type, $storeId)
    {
        $metaId = $this->getMetaId($type, $storeId);
        if ($metaId) {
            $prefix = $this->getPrefix($metaId);
            $suffix = $this->getSuffix($metaId);
            return "/{$prefix}[0-9]{9}{$suffix}/";
        }
        return false;
    }

    /**
     * @param $metaId
     * @return mixed|string
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getPrefix($metaId)
    {
        if (null == $this->prefixs) {
            $this->prepareProfileList();
        }

        return isset($this->prefixs[$metaId]) ? $this->prefixs[$metaId] : '';
    }

    /**
     * @param $metaId
     * @return mixed|string
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getSuffix($metaId)
    {
        if (null == $this->suffixs) {
            $this->prepareProfileList();
        }

        return isset($this->suffixs[$metaId]) ? $this->suffixs[$metaId] : '';
    }

    /**
     * @param $type
     * @param $storeId
     * @return bool|mixed
     * @throws \Zend_Db_Statement_Exception
     */
    protected function getMetaId($type, $storeId)
    {
        if (null == $this->metas) {
            $this->prepareMetaList();
        }

        return isset($this->metas[$type.$storeId]) ? $this->metas[$type.$storeId] : false;
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    protected function prepareMetaList()
    {
        $select = $this->connection->select();
        $select->from($this->getMetaTable(), ['meta_id', 'entity_type', 'store_id']);

        $result = $this->connection->query($select);
        while ($row = $result->fetch()) {
            $this->metas[$row['entity_type'] . $row['store_id']] = $row['meta_id'];
        }
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     */
    protected function prepareProfileList()
    {
        $select = $this->connection->select();
        $select->from($this->getProfileTable(), ['meta_id', 'prefix', 'suffix']);

        $result = $this->connection->query($select);
        while ($row = $result->fetch()) {
            $this->prefixs[$row['meta_id']] = $row['prefix'];
            $this->suffixs[$row['meta_id']] = $row['suffix'];
        }
    }

    /**
     * @return string
     */
    protected function getProfileTable()
    {
        return $this->resource->getTableName($this->profileTableName);
    }

    /**
     * @return string
     */
    protected function getMetaTable()
    {
        return $this->resource->getTableName($this->metaTableName);
    }
}
