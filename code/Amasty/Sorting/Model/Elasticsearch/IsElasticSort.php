<?php

declare(strict_types=1);

namespace Amasty\Sorting\Model\Elasticsearch;

use Magento\CatalogSearch\Model\ResourceModel\EngineInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManager;

class IsElasticSort
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var StoreManager
     */
    private $storeManager;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManager $storeManager
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param bool $skipStoreCheck
     * @return bool
     */
    public function execute(bool $skipStoreCheck = false): bool
    {
        return strpos($this->scopeConfig->getValue(EngineInterface::CONFIG_ENGINE_PATH), 'elast') !== false
            && ($skipStoreCheck || $this->storeManager->getStore()->getId());
    }
}
