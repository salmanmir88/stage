<?php

namespace Amasty\Sorting\Model\ResourceModel;

class Inventory extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * @var array
     */
    private $stockIds;

    /**
     * @var array
     */
    private $sourceCodes;

    /**
     * @var array
     */
    private $qty;

    /**
     * @var array
     */
    private $stockStatus;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var QtyTypeResolver
     */
    private $qtyTypeResolver;

    public function __construct(
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        QtyTypeResolver $qtyTypeResolver,
        $connectionName = null
    ) {
        parent::__construct($context, $connectionName);
        $this->moduleManager = $moduleManager;
        $this->stockRegistry = $stockRegistry;
        $this->qtyTypeResolver = $qtyTypeResolver;
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->stockIds = [];
        $this->sourceCodes = [];
        $this->qty = [];
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return float|int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getQty($productSku, $websiteCode)
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $qty = $this->getMsiQty($productSku, $websiteCode);
        } else {
            $qty = $this->getStockItem($productSku, $websiteCode)->getQty();
        }

        return $qty;
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStockStatus($productSku, $websiteCode)
    {
        if ($this->moduleManager->isEnabled('Magento_Inventory')) {
            $stockStatus = $this->getMsiStock($productSku, $websiteCode);
        } else {
            $stockStatus = $this->getStockItem($productSku, $websiteCode)->getIsInStock();
        }

        return $stockStatus;
    }

    /**
     * @param $productSku
     * @param $websiteCode
     *
     * @return \Magento\CatalogInventory\Api\Data\StockItemInterface
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getStockItem($productSku, $websiteCode)
    {
        return $this->stockRegistry->getStockItemBySku($productSku, $websiteCode);
    }

    /**
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMsiStock($productSku, $websiteCode)
    {
        if (!isset($this->stockStatus[$websiteCode][$productSku])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_' . $this->getStockId($websiteCode)), ['is_salable'])
                ->where('sku = ?', $productSku)
                ->group('sku');
            $this->stockStatus[$websiteCode][$productSku] = (int) $this->getConnection()->fetchOne($select);
        }

        return $this->stockStatus[$websiteCode][$productSku];
    }

    /**
     * For MSI. Need to get negative qty.
     * Emulate \Magento\InventoryReservations\Model\ResourceModel\GetReservationsQuantity::execute
     *
     * @param string $productSku
     * @param string $websiteCode
     *
     * @return float|int
     *
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getMsiQty($productSku, $websiteCode)
    {
        if (!isset($this->qty[$websiteCode][$productSku])) {
            $stockId = $this->getStockId($websiteCode);
            $qty = $this->qtyTypeResolver->getQtyBySku($stockId, [$productSku])[$productSku];
            $this->qty[$websiteCode][$productSku] = $qty
                + $this->getReservationQty($productSku, $stockId);
        }

        return $this->qty[$websiteCode][$productSku];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return int
     */
    public function getStockId(string $websiteCode): int
    {
        if (!isset($this->stockIds[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_stock_sales_channel'), ['stock_id'])
                ->where('type = \'website\' AND code = ?', $websiteCode);

            $this->stockIds[$websiteCode] = (int)$this->getConnection()->fetchOne($select);
        }

        return $this->stockIds[$websiteCode];
    }

    /**
     * For MSI.
     *
     * @param string $websiteCode
     *
     * @return array
     */
    public function getSourceCodes($websiteCode)
    {
        if (!isset($this->sourceCodes[$websiteCode])) {
            $select = $this->getConnection()->select()
                ->from($this->getTable('inventory_source_stock_link'), ['source_code'])
                ->where('stock_id = ?', $this->getStockId($websiteCode));

            $this->sourceCodes[$websiteCode] = $this->getConnection()->fetchCol($select);
        }

        return $this->sourceCodes[$websiteCode];
    }

    /**
     * For MSI.
     *
     * @param string $sku
     * @param int $stockId
     *
     * @return int|string
     */
    private function getReservationQty($sku, $stockId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getTable('inventory_reservation'), ['quantity' => 'SUM(quantity)'])
            ->where('sku = ?', $sku)
            ->where('stock_id = ?', $stockId)
            ->limit(1);

        $reservationQty = $this->getConnection()->fetchOne($select);
        if ($reservationQty === false) {
            $reservationQty = 0;
        }

        return $reservationQty;
    }
}
