<?php

namespace Kpopia\CustomWork\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;

class UpdateCategoryName implements ObserverInterface
{
    protected $resourceConnection;
    protected $productRepository;
    protected $categoryCollectionFactory;

    public function __construct(
        ResourceConnection $resourceConnection,
        ProductRepository $productRepository,
        CategoryCollectionFactory $categoryCollectionFactory
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        $orderItems = $order->getItems();

        foreach ($orderItems as $item) {
            $sku = $item->getSku();

            try {
                // Load the product by SKU
                $product = $this->productRepository->get($sku);

                // Get the category IDs
                $categoryIds = $product->getCategoryIds();

                $categoryIdsString = implode(', ', $categoryIds);

                // Update category_id in sales_order_item table
                $connection = $this->resourceConnection->getConnection();
                $tableName = $this->resourceConnection->getTableName('sales_order_item');
                $connection->update(
                    $tableName,
                    ['category_id' => $categoryIdsString],
                    ['item_id = ?' => $item->getId()]
                );
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                // Handle cases where the SKU does not correspond to an existing product
                continue;
            }
        }
    }
}
