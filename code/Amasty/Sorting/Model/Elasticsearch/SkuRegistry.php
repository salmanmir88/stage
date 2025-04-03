<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Elasticsearch;

use Amasty\Sorting\Model\ResourceModel\LoadSkuMap;

class SkuRegistry
{
    /**
     * Where key is entityId; value is sku.
     * @var array|null
     */
    private $skuRelations;

    /**
     * @var LoadSkuMap
     */
    private $loadSkuMap;

    public function __construct(LoadSkuMap $loadSkuMap)
    {
        $this->loadSkuMap = $loadSkuMap;
    }

    public function save(array $entityIds)
    {
        $this->skuRelations = $this->loadSkuMap->execute($entityIds);
    }

    public function clear()
    {
        $this->skuRelations = null;
    }

    public function getSku(int $entityId): string
    {
        return $this->skuRelations[$entityId] ?? '';
    }
}
