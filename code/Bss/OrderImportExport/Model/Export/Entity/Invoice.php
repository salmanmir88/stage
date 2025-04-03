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
namespace Bss\OrderImportExport\Model\Export\Entity;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\ImportExport\Model\Export\Factory as ExportFactory;
use Magento\ImportExport\Model\ResourceModel\CollectionByPagesIteratorFactory;
use Magento\Store\Model\StoreManagerInterface;
use Bss\OrderImportExport\Model\Import\Constant;

class Invoice extends AbstractEntity
{
    /**
     * Current Entity Id Column
     */
    const COLUMN_ENTITY_ID = 'entity_id';

    /**
     * Parent Entity Id Column
     */
    const COLUMN_PARENT_ID = 'order_id';

    /**
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_INVOICE;

    /**
     * Table name for entity
     *
     * @var string
     */
    protected $mainTable = 'sales_invoice';

    /**
     * @var Invoice\Item
     */
    protected $itemEntity;

    /**
     * @var Invoice\Comment
     */
    protected $commentEntity;

    /**
     * Invoice constructor.
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param ExportFactory $collectionFactory
     * @param CollectionByPagesIteratorFactory $resourceColFactory
     * @param ResourceConnection $resource
     * @param Invoice\ItemFactory $itemFactory
     * @param Invoice\CommentFactory $commentFactory
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        ExportFactory $collectionFactory,
        CollectionByPagesIteratorFactory $resourceColFactory,
        ResourceConnection $resource,
        Invoice\ItemFactory $itemFactory,
        Invoice\CommentFactory $commentFactory
    ) {
        parent::__construct($scopeConfig, $storeManager, $collectionFactory, $resourceColFactory, $resource);
        $this->itemEntity = $itemFactory->create();
        $this->commentEntity = $commentFactory->create();
    }

    /**
     * List of children entity
     *
     * @return array
     */
    protected function getChildren()
    {
        return [
            $this->itemEntity,
            $this->commentEntity
        ];
    }
}
