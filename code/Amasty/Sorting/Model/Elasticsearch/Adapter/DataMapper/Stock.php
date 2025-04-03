<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapperInterface;
use Amasty\Sorting\Model\Elasticsearch\SkuRegistry;
use Amasty\Sorting\Model\ResourceModel\Inventory;
use Magento\Store\Model\StoreManagerInterface;

class Stock implements DataMapperInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var Inventory
     */
    private $inventory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SkuRegistry
     */
    private $skuRegistry;

    public function __construct(
        Data $data,
        Inventory $inventory,
        StoreManagerInterface $storeManager,
        SkuRegistry $skuRegistry
    ) {
        $this->data = $data;
        $this->inventory = $inventory;
        $this->storeManager = $storeManager;
        $this->skuRegistry = $skuRegistry;
    }

    public function map(int $entityId, array $entityIndexData, int $storeId, ?array $context = []): array
    {
        $sku = $this->skuRegistry->getSku((int) $entityId);

        if (!$sku) {
            return ['out_of_stock_last' => true];
        }

        if ($this->data->isOutOfStockByQty($storeId)) {
            $currentQty = $this->inventory->getQty(
                $sku,
                $this->storeManager->getStore($storeId)->getWebsite()->getCode()
            );
            $value = (int) ($currentQty > $this->data->getQtyOutStock($storeId));
        } else {
            $value = (int) $this->inventory->getStockStatus(
                $sku,
                $this->storeManager->getStore($storeId)->getWebsite()->getCode()
            );
        }

        return ['out_of_stock_last' => $value];
    }

    public function isAllowed(int $storeId): bool
    {
        return (bool) $this->data->getOutOfStockLast($storeId);
    }
}
