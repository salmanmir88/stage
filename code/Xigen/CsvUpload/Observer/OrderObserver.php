<?php

namespace Xigen\CsvUpload\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem;
use Magento\Framework\File\Csv;
use Magento\Catalog\Model\CategoryLinkRepository;

class OrderObserver implements ObserverInterface
{
    private $productRepositoryInterface;
    protected $resourceConnection;
    protected $filesystem;
    protected $csvProcessor;
    protected $categoryLinkRepository;

    public function __construct(
        ProductRepositoryInterface $productRepositoryInterface,
        ResourceConnection $resourceConnection,
        CategoryLinkRepository $categoryLinkRepository,
        Filesystem $filesystem,
        Csv $csvProcessor
    ) {
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->resourceConnection = $resourceConnection;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->filesystem = $filesystem;
        $this->csvProcessor = $csvProcessor;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        // Get the ordered products and their quantities
        $orderedProducts = $order->getAllItems();
        $categoryId = 90;
        $skus = [];
        foreach ($orderedProducts as $item) {
            $sku = $item->getSku();
            $qty = $item->getQtyOrdered();
            $product = $this->productRepositoryInterface->get($sku, false); // Use the product repository to get the product instance
            $specialQty = $product->getSpecialPriceQuantity();
            if ($specialQty) {
                $qty = $specialQty - $qty;
                $product->setSpecialPriceQuantity($qty);
                // Save the product instance to persist the changes
                $this->productRepositoryInterface->save($product);
                $specialPriceQuantity = $product->getSpecialPriceQuantity();
                if ($specialPriceQuantity <= 0) {
                    $skus[] = $sku;
                }
                // Fetch processed records from the `xigen_csvupload_csv` table
                if ($sku) {
                    $this->fetchProcessedCsvRecords($specialPriceQuantity, $sku);
                }
            }
        }
        if ($skus) {
            $this->categoryLinkRepository->deleteBySkus($categoryId, $skus);
        }
        return $this;
    }

    /**
     * Fetch records where processed = 1 from the xigen_csvupload_csv table
     */
    private function fetchProcessedCsvRecords($specialPriceQuantity, $sku)
    {
        // Get the connection to the database
        $connection = $this->resourceConnection->getConnection();
        // Define the table name
        $tableName = $this->resourceConnection->getTableName('xigen_csvupload_csv');
        // Prepare the SQL query
        $sql = "SELECT `csv_id`, `rulename`, `filename`, `created_at`, `applied_at`, `locked`, `processed`, `locked_to_remove`, `job_in_process`
                FROM `{$tableName}`
                WHERE `processed` = 1";
        // Execute the query
        $result = $connection->fetchAll($sql);
        // Example: Do something with the results
        foreach ($result as $row) {
            $fileName = $row['filename'];
        }
        $filePath = $this->filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::PUB)
            ->getAbsolutePath();
        $fileUrlPath = parse_url($fileName, PHP_URL_PATH);
        // Combine both paths
        $finalFilePath = rtrim($filePath, '/') . $fileUrlPath;

        // Load existing CSV data
        $data = $this->csvProcessor->getData($finalFilePath);

        // Check if the CSV file has data
        if (!empty($data)) {
            // Check if 'remaining_qty' column exists
            $header = $data[0];
            if (!in_array('remaining_qty', $header)) {
                // Add the 'remaining_qty' column if it doesn't exist
                $header[] = 'remaining_qty';
                $data[0] = $header;
            }

            // Find the index of the SKU column and remaining_qty column
            $skuColumnIndex = array_search('sku', $header);
            $remainingQtyIndex = array_search('remaining_qty', $header);

            // Add data for 'remaining_qty' column only for the row with matching SKU
            for ($i = 1; $i < count($data); $i++) {
                if (isset($data[$i][$skuColumnIndex]) && $data[$i][$skuColumnIndex] === $sku) {
                    $data[$i][$remainingQtyIndex] = $specialPriceQuantity;
                }
            }

            // Save the updated data back to the CSV file
            $this->csvProcessor->saveData($finalFilePath, $data);
        }
    }
}
