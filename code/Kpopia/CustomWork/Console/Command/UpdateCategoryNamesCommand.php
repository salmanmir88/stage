<?php

namespace Kpopia\CustomWork\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Framework\App\ResourceConnection;

class UpdateCategoryNamesCommand extends Command
{
    private $resourceConnection;
    private $productRepository;
    private $categoryCollectionFactory;
    private $state;

    public function __construct(
        ResourceConnection $resourceConnection,
        ProductRepository $productRepository,
        CategoryCollectionFactory $categoryCollectionFactory,
        State $state
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->state = $state;
        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('kpopia:update-category-names')
            ->setDescription('Update category names for all order items based on their product categories.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_ADMINHTML);

        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order_item');

        $select = $connection->select()->from($tableName, ['item_id', 'sku']);
        $orderItems = $connection->fetchAll($select);

        $output->writeln("Found " . count($orderItems) . " order items to update.");

        $successCount = 0;
        $failureCount = 0;

        foreach ($orderItems as $item) {
            $sku = $item['sku'];
            try {
                $product = $this->productRepository->get($sku);
                $categoryIds = $product->getCategoryIds();

                if (!empty($categoryIds)) {
                    // Fetch category names based on IDs
                    $categoryCollection = $this->categoryCollectionFactory->create()
                        ->addAttributeToSelect('name')
                        ->addAttributeToFilter('entity_id', ['in' => $categoryIds]);

                    $categoryNames = [];
                    foreach ($categoryCollection as $category) {
                        $categoryNames[] = $category->getName();
                    }

                    $categoryNamesString = implode(', ', $categoryNames);

                    // Update the order item with category names
                    $connection->update(
                        $tableName,
                        ['category_id' => $categoryNamesString],
                        ['item_id = ?' => $item['item_id']]
                    );

                    $output->writeln("Updated category names for order item #" . $item['item_id']);
                    $successCount++;
                } else {
                    throw new \Exception("No categories found for product SKU: $sku");
                }
            } catch (\Exception $e) {
                $output->writeln("Failed to update category names for order item #" . $item['item_id'] . ": " . $e->getMessage());
                $failureCount++;
            }
        }

        $output->writeln("Summary:");
        $output->writeln("  Success: " . $successCount);
        $output->writeln("  Failure: " . $failureCount);

        return Command::SUCCESS;
    }
}

