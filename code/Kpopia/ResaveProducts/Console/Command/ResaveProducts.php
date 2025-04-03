<?php

namespace Kpopia\ResaveProducts\Console\Command;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\State;
use Magento\Framework\Console\Cli;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Store\Model\StoreManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Catalog\Model\ProductRepository;

class ResaveProducts extends Command
{
    const BATCH_SIZE = 500; // Number of products to process in one batch

    private $state;
    private $productCollectionFactory;
    private $productRepository;
    private $storeManager;

    public function __construct(
        State $state,
        CollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        StoreManagerInterface $storeManager
    ) {
        $this->state = $state;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->storeManager = $storeManager;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setName('kpopia:resave:products')
            ->setDescription('Resave all products in batches to avoid server overload.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(\Magento\Framework\App\Area::AREA_GLOBAL);

        $storeId = $this->storeManager->getDefaultStoreView()->getId();
        $productCollection = $this->productCollectionFactory->create();
        $productCollection->addStoreFilter($storeId)
            ->addAttributeToSelect('*')
            ->setPageSize(self::BATCH_SIZE);

        $totalProducts = $productCollection->getSize();
        $pages = ceil($totalProducts / self::BATCH_SIZE);
        $output->writeln("Found $totalProducts products. Processing in $pages batches.");

        for ($currentPage = 1; $currentPage <= $pages; $currentPage++) {
            $productCollection->setCurPage($currentPage);

            foreach ($productCollection as $product) {
                try {
                    $this->productRepository->save($product);
                    $output->writeln("Product ID {$product->getId()} resaved.");
                } catch (\Exception $e) {
                    $output->writeln("Failed to resave Product ID {$product->getId()}: " . $e->getMessage());
                }
            }

            $productCollection->clear(); // Clear collection and free memory
        }

        $output->writeln("Resaving completed.");
        return Cli::RETURN_SUCCESS;
    }
}
