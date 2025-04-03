<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Elasticsearch\Adapter;

use Amasty\Sorting\Model\ResourceModel\Method\AbstractMethod;
use Amasty\Sorting\Helper\Data;

abstract class IndexedDataMapper implements DataMapperInterface
{
    const DEFAULT_VALUE = 0;

    /**
     * @var AbstractMethod
     */
    protected $resourceMethod;

    /**
     * @var array
     */
    protected $values = [];

    /**
     * @var Data
     */
    private $helper;

    public function __construct(
        AbstractMethod $resourceMethod,
        Data $helper
    ) {
        $this->resourceMethod = $resourceMethod;
        $this->helper = $helper;
    }

    /**
     * @return string
     */
    abstract public function getIndexerCode();

    /**
     * @param int $storeId
     * @param array|null $entityIds
     * @return array
     */
    protected function forceLoad(int $storeId, ?array $entityIds = []): array
    {
        return $this->resourceMethod->getIndexedValues($storeId, $entityIds);
    }

    public function isAllowed(int $storeId): bool
    {
        return !$this->helper->isMethodDisabled($this->resourceMethod->getMethodCode(), $storeId);
    }

    public function map(int $entityId, array $entityIndexData, int $storeId, ?array $context = []): array
    {
        $value = isset($this->values[$storeId][$entityId]) ? $this->values[$storeId][$entityId] : self::DEFAULT_VALUE;

        return [static::FIELD_NAME => $value];
    }

    public function loadEntities(int $storeId, array $entityIds): void
    {
        if (!$this->values) {
            $this->values[$storeId] = $this->forceLoad($storeId, $entityIds);
        }
    }

    public function clearValues(): void
    {
        $this->values = null;
    }
}
