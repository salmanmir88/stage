<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Elasticsearch\Adapter\IndexedDataMapper;
use Amasty\Sorting\Model\ResourceModel\Method\Saving as SavingResource;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class Saving extends IndexedDataMapper
{
    const FIELD_NAME = 'saving';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        SavingResource $resourceMethod,
        Data $helper
    ) {
        parent::__construct($resourceMethod, $helper);
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Saving is not indexed method.
     * @return bool
     */
    public function getIndexerCode()
    {
        return false;
    }

    protected function forceLoad(int $storeId, ?array $entityIds = []): array
    {
        /** @var Collection $collection */
        $collection = $this->collectionFactory->create();
        $collection->setStoreId($storeId);
        $collection->addPriceData();
        $this->resourceMethod->setLimitColumns(true);
        $this->resourceMethod->apply($collection, '');
        return $this->resourceMethod->getConnection()->fetchPairs($collection->getSelect());
    }
}
