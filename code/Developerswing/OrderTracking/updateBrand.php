<?php
use Magento\Framework\App\Bootstrap;
use Magento\Eav\Api\AttributeRepositoryInterface;
require 'app/bootstrap.php';

// Initialize Magento application
$bootstrap = Bootstrap::create(BP, $_SERVER);
$objectManager = $bootstrap->getObjectManager();
$appState = $objectManager->get('Magento\Framework\App\State');
$appState->setAreaCode('adminhtml');

// Load required classes
$productRepository = $objectManager->get('Magento\Catalog\Api\ProductRepositoryInterface');
$attributeRepository = $objectManager->get(AttributeRepositoryInterface::class);

// Path to the CSV file
$csvFile = 'brand.csv';
if (!file_exists($csvFile)) {
    die("CSV file not found at $csvFile\n");
}

// Read CSV file
$csvData = array_map('str_getcsv', file($csvFile));
$headers = array_shift($csvData); // Assume the first row contains headers

// Arrays to collect issues
$missingOptions = [];  // Collect labels for which no matching option ID was found
$failedProducts = [];   // Collect products where there was an issue saving

foreach ($csvData as $row) {
    $rowData = array_combine($headers, $row);
    $sku = $rowData['sku'] ?? null;
    $attributeCode = $rowData['attribute_code'] ?? null;
    $labelToAssign = $rowData['value'] ?? null;

    if (!$sku || !$attributeCode || !$labelToAssign) {
        echo "Invalid data in CSV row: " . implode(',', $row) . "\n";
        continue;
    }

    try {
        // Load the product by SKU
        $product = $productRepository->get($sku);

        // Load the attribute by code
        $attribute = $attributeRepository->get('catalog_product', $attributeCode);

        // Ensure the attribute is of type dropdown
        if ($attribute->getFrontendInput() !== 'select') {
            echo "The attribute $attributeCode is not a dropdown (select) attribute.\n";
            continue;
        }

        // Get the options of the attribute
        $options = $attribute->getOptions();

        // Find the option ID for the given label
        $optionIdToAssign = null;
        foreach ($options as $option) {
            if (strtolower($option->getLabel()) == strtolower($labelToAssign)) {
                $optionIdToAssign = $option->getValue();
                break;
            }
        }

        if (!$optionIdToAssign) {
            echo "Option label '$labelToAssign' not found for attribute $attributeCode.\n";
            // Store the missing label
            $missingOptions[] = [
                'sku' => $sku,
                'attribute_code' => $attributeCode,
                'label' => $labelToAssign
            ];
            continue;
        }

        echo "Assigning option ID $optionIdToAssign for label '$labelToAssign' to product SKU $sku.\n";

        // Set the attribute value (for dropdown, only one value is allowed)
        $product->setData($attributeCode, $optionIdToAssign);

        // Save the product
        $product->getResource()->saveAttribute($product, $attributeCode);

        echo "Successfully updated product SKU $sku with attribute $attributeCode.\n";
    } catch (Exception $e) {
        echo "Error processing SKU $sku: " . $e->getMessage() . "\n";
        // Store the failed product
        $failedProducts[] = [
            'sku' => $sku,
            'error_message' => $e->getMessage()
        ];
    }
}

// Output the missing options and failed products at the end
if (!empty($missingOptions)) {
    echo "\nMissing Option Labels:\n";
    foreach ($missingOptions as $missing) {
        echo "SKU: {$missing['sku']}, Attribute: {$missing['attribute_code']}, Label: {$missing['label']}\n";
    }
}

if (!empty($failedProducts)) {
    echo "\nFailed Products:\n";
    foreach ($failedProducts as $failed) {
        echo "SKU: {$failed['sku']}, Error: {$failed['error_message']}\n";
    }
}
?>
