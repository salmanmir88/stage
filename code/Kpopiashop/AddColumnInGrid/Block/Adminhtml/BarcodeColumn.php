<?php

namespace Kpopiashop\AddColumnInGrid\Block\Adminhtml;

use Magento\InventoryCatalogApi\Model\IsSingleSourceModeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\InventoryApi\Api\SourceItemRepositoryInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;

class BarcodeColumn extends \Magento\Sales\Block\Adminhtml\Items\Column\DefaultColumn
{

        /**
     * @var IsSingleSourceModeInterface
     */
    private $isSingleSourceMode;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SourceItemRepositoryInterface
     */
    private $sourceItemRepository;

    private $productRepository; 

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\Product\OptionFactory $optionFactory,
        IsSingleSourceModeInterface $isSingleSourceMode,
        SourceRepositoryInterface $sourceRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SourceItemRepositoryInterface $sourceItemRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = []
    ) {
        $this->_optionFactory = $optionFactory;
        $this->isSingleSourceMode = $isSingleSourceMode;
        $this->sourceRepository = $sourceRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sourceItemRepository = $sourceItemRepository;
        $this->productRepository = $productRepository;
        parent::__construct($context, $stockRegistry, $stockConfiguration, $registry, $optionFactory, $data);

    }

    public function getBarcode($itemsBySkus)
    {
        try {
            $product = $this->productRepository->get($itemsBySkus);
            return $product->getBarcode();
        } catch (\Exception $e) {
            
        }
        
    }
}