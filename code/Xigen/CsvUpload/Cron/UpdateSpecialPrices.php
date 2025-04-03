<?php

namespace Xigen\CsvUpload\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Catalog\Model\ProductFactory;

class UpdateSpecialPrices
{
    protected $productCollectionFactory;
    protected $productRepository;
    protected $stockRegistry;
    protected $categoryLinkRepository;
    protected $productFactory;
    protected $logger;

    public function __construct(
        CollectionFactory $productCollectionFactory,
        ProductRepositoryInterface $productRepository,
        CategoryLinkRepository $categoryLinkRepository,
        ProductFactory $productFactory,
        StockRegistryInterface $stockRegistry,
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->productFactory = $productFactory;
        $this->stockRegistry = $stockRegistry;
    }

    public function execute()
    {
        $collection = $this->productCollectionFactory->create()
            ->addAttributeToSelect(['sku', 'entity_id'])
            ->addAttributeToFilter('special_price_quantity', ['lteq' => '0']);
        $skus = [];
        $categoryId = 90;
        foreach ($collection as $product) {
            // $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\ProductFactory::class);
            $product = $this->productFactory->create()->load($product->getId());
            $specialPrice = $product->getSpecialDiscountPrice();
            if ($specialPrice !== null) {
                $product->setSpecialPrice($specialPrice);
                $product->setSpecialDiscountPrice(null);
                $product->setSpecialPriceQuantity(null);
                $product->save();
            } else {
                $product->setSpecialPrice(null);
                $product->setSpecialDiscountPrice(null);
                $product->setSpecialPriceQuantity(null);
                $product->save();
            }
            $skus[] = $product->getSku();
        }
        if ($skus) {
            $this->categoryLinkRepository->deleteBySkus($categoryId, $skus);
        }
    }
}
