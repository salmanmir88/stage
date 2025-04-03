<?php

namespace Xigen\CsvUpload\Cron;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Catalog\Model\ProductFactory;

class ProcessOutputCsv
{
    protected $filesystem;
    protected $messageManager;
    protected $notifier;
    protected $productRepository;
    protected $searchCriteriaBuilder;
    protected $filterBuilder;
    protected $stockRegistry;
    protected $productSalableChecker;
    protected $productFactory;

    public function __construct(
        Filesystem $filesystem,
        ManagerInterface $messageManager,
        NotifierInterface $notifier,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        StockRegistryInterface $stockRegistry,
        IsProductSalableInterface $productSalableChecker,
        ProductFactory $productFactory
    ) {
        $this->filesystem = $filesystem;
        $this->messageManager = $messageManager;
        $this->notifier = $notifier;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->stockRegistry = $stockRegistry;
        $this->productSalableChecker = $productSalableChecker;
        $this->productFactory = $productFactory;
    }

    public function execute()
    {
        $importDir = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath() . '/import/barcodecustomcsv/';
        $exportDir = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath() . '/import/outputcsv/';
        if (!is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }
        // Iterate over CSV files in the import directory
        foreach (glob($importDir . '*.csv') as $inputCsv) {
            $tempCsv = tempnam(sys_get_temp_dir(), 'output_');
            $outputCsv = $exportDir . 'Output-' . basename($inputCsv);

            // Process the CSV file
            $this->processCsv($inputCsv, $tempCsv);

            // Move the temp file to the export directory once processing is complete
            if (file_exists($tempCsv)) {
                rename($tempCsv, $outputCsv);

                // Set read and write permissions on the output CSV
                chmod($outputCsv, 0666); // Read and write permissions for everyone

                // Add an admin notification for the generated output file
                $this->notifier->addNotice(
                    __('Your export file is ready'),
                    __('You can pick up your file at export main page')
                );
            }
        }
    }


    private function processCsv($inputCsv, $tempCsv)
    {
        try {
            $processedDir = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath() . '/import/processed/';
            // Ensure the processed directory exists
            if (!is_dir($processedDir)) {
                mkdir($processedDir, 0777, true);
            }
            if (!file_exists($inputCsv)) {
                throw new \Exception(__('Input CSV file not found.'));
            }

            $inputFile = fopen($inputCsv, 'r');
            $outputFile = fopen($tempCsv, 'w');

            // Read existing headers from the input file
            $existingHeaders = fgetcsv($inputFile);
            $newHeaders = ['sku', 'available', 'is_instock', 'is_salable', 'sell_price', 'album_qyt'];

            // Check if new headers already exist
            foreach ($newHeaders as $header) {
                if (!in_array($header, $existingHeaders)) {
                    $existingHeaders[] = $header;
                }
            }
            // Move the processed CSV to the processed directory
            rename($inputCsv, $processedDir . basename($inputCsv));
            // Write updated headers to the output file
            fputcsv($outputFile, $existingHeaders);

            while (($row = fgetcsv($inputFile)) !== false) {
                $data = array_combine(array_slice($existingHeaders, 0, count($row)), $row);
                $barcode = trim($data['barcode']);
                $skus = $this->getSkusByBarcode($barcode);

                if (!empty($skus)) {
                    foreach ($skus as $sku) {
                        $stockInfo = $this->checkStockInfo($sku);
                        $sellPrice = $this->getSellPrice($sku);
                        $albumQty = $this->getAlbumQty($sku);
                        $data['sku'] = $sku;
                        $data['available'] = 'yes';
                        $data['is_instock'] = $stockInfo['is_instock'];
                        $data['is_salable'] = $stockInfo['is_salable'];
                        $data['sell_price'] = $sellPrice;
                        $data['album_qyt'] = $albumQty;
                        fputcsv($outputFile, $data);
                    }
                } else {
                    $data['sku'] = '';
                    $data['available'] = 'no';
                    $data['is_instock'] = 'no';
                    $data['is_salable'] = 'no';
                    $data['album_qyt'] = '';
                    fputcsv($outputFile, $data);
                }
            }

            fclose($inputFile);
            fclose($outputFile);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Error processing CSV: %1', $e->getMessage()));
        }
    }

    private function getAlbumQty($sku)
    {
        $product = $this->productRepository->get($sku);
        $product = $this->productFactory->create()->load($product->getId());
        return $product->getAlbumQyt();
    }
    private function getSkusByBarcode($barcode)
    {
        $filter = $this->filterBuilder->setField('barcode')
            ->setValue('%' . $barcode . '%')
            ->setConditionType('like')
            ->create();
        $searchCriteria = $this->searchCriteriaBuilder->addFilters([$filter])->create();
        $productList = $this->productRepository->getList($searchCriteria);
        return array_map(fn($product) => $product->getSku(), $productList->getItems());
    }

    private function checkStockInfo($sku)
    {
        $stockItem = $this->stockRegistry->getStockItemBySku($sku);
        return [
            'is_instock' => $stockItem->getIsInStock() ? 'yes' : 'no',
            'is_salable' => $this->productSalableChecker->execute($sku, 1) ? 'yes' : 'no'
        ];
    }

    private function getSellPrice($sku)
    {
        $product = $this->productRepository->get($sku);

        $actualPrice = $product->getPrice();
        $specialPrice = $product->getSpecialPrice();

        // Check if special price is valid (within date range)
        $specialFromDate = $product->getSpecialFromDate();
        $specialToDate = $product->getSpecialToDate();
        $currentDate = date('Y-m-d H:i:s');

        // Validate special price
        if ($specialPrice) {
            if (($specialFromDate && $specialFromDate > $currentDate) || ($specialToDate && $specialToDate < $currentDate)) {
                $specialPrice = null; // Special price is not valid
            }
        }

        // Handle tier price, get the minimum tier price if available
        $tierPrices = $product->getTierPrice();
        if (is_array($tierPrices) && count($tierPrices) > 0) {
            $tierPrice = min(array_column($tierPrices, 'price'));
        } else {
            $tierPrice = null;
        }

        // Filter out null values
        $prices = array_filter([$actualPrice, $specialPrice, $tierPrice], function ($price) {
            return $price !== null;
        });

        // Get the minimum value among the prices
        $minPrice = !empty($prices) ? min($prices) : null;

        return $minPrice;
    }
}
