<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;

class LoadSkuMap
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    public function execute(array $entityIds): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            $this->resourceConnection->getTableName('catalog_product_entity'),
            ['entity_id', 'sku']
        )->where('entity_id IN (?)', $entityIds);

        return $this->resourceConnection->getConnection()->fetchPairs($select);
    }
}
