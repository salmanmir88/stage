<?php

namespace Xigen\CsvUpload\Cron;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Notification\NotifierInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\File\Csv;
use Magento\Catalog\Model\ProductFactory;
use Xigen\CsvUpload\Model\CsvFactory;
use Xigen\CsvUpload\Helper\Csv as XigenCsvHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Xigen CsvUpload Process controller class
 */
class ApplySpecialPrice
{
    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Csv
     */
    private $csvProcessor;

    /**
     * @var StockRegistryInterface;
     */
    private $stockRegistry;

    /**
     * @var CsvFactory
     */
    private $csvFactory;

    /**
     * @var XigenCsvHelper
     */
    private $csvHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var DateTime
     */
    private $dateTime;

    protected $productFactory;

    private $productRepositoryInterface;

    protected $productCollectionFactory;

    protected $categoryLinkRepository;

    protected $notifier;

    /**
     * Process constructor.
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param Csv $csvProcessor
     * @param CsvFactory $csvFactory
     * @param XigenCsvHelper $csvHelper
     * @param StoreManagerInterface $storeManager
     * @param StockRegistryInterface $stockRegistry
     * @param DirectoryList $directoryList
     * @param DateTime $dateTime
     * @param ProductRepositoryInterface $productRepositoryInterface
     * @param ProductCollectionFactory $productCollectionFactory
     * @param NotifierInterface $notifier
     * @param CategoryLinkRepository $categoryLinkRepository
     * @param ProductFactory $productFactory
     */
    public function __construct(
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        Csv $csvProcessor,
        CsvFactory $csvFactory,
        XigenCsvHelper $csvHelper,
        StoreManagerInterface $storeManager,
        StockRegistryInterface $stockRegistry,
        DirectoryList $directoryList,
        DateTime $dateTime,
        ProductRepositoryInterface $productRepositoryInterface,
        ProductCollectionFactory $productCollectionFactory,
        CategoryLinkRepository $categoryLinkRepository,
        ProductFactory $productFactory,
        NotifierInterface $notifier
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->csvProcessor = $csvProcessor;
        $this->csvFactory = $csvFactory;
        $this->notifier = $notifier;
        $this->csvHelper = $csvHelper;
        $this->stockRegistry = $stockRegistry;
        $this->directoryList = $directoryList;
        $this->dateTime = $dateTime;
        $this->storeManager = $storeManager;
        $this->productRepositoryInterface = $productRepositoryInterface;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->productFactory = $productFactory;
    }

    /**
     * Edit action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $lockToApplyId = $this->csvHelper->getLockedToApplyCsvFileId();
        $isLockedToApply = $this->csvHelper->isAnyCsvFileLockedToApply();
        $isApplied = $this->csvHelper->isAnyCsvFileApplied();
        $inProcess = $this->csvHelper->isAnyJobInProcess();
        if ($inProcess) {
            return true;
        }
        $applyId = $this->csvHelper->getAppliedCsvFileId();
        if ($isLockedToApply && $isApplied) {

            if ($lockToApplyId === $applyId) {
                $this->csvHelper->setCsvFileLockedToApplyToId($lockToApplyId, 0);
                return;
            } else {
                $this->csvHelper->setJobInProcessToId($lockToApplyId, 1);
                $collection = $this->productCollectionFactory->create()
                    ->addAttributeToFilter('special_discount_price', ['notnull' => true]);
                if ($collection) {
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

                $this->csvHelper->resetValueForAppliedFiles();
                $this->applyPriceRule($lockToApplyId);
                // Add an admin notification for the generated output file
                $this->notifier->addNotice(
                    __('Special Price Rule Applied Successfully for Rule ' . $lockToApplyId),
                    __('')
                );
            }
        } elseif ($isLockedToApply && !$isApplied) {
            $this->csvHelper->setJobInProcessToId($lockToApplyId, 1);
            $this->applyPriceRule($lockToApplyId);
            // Add an admin notification for the generated output file
            $this->notifier->addNotice(
                __('Special Price Rule Applied Successfully for Rule ' . $lockToApplyId),
                __('')
            );
        }
    }


    private function applyPriceRule($lockToApplyId)
    {
        $path = $this->csvHelper->getFilepath();
        $file = $this->csvHelper->getLockedToApplyCsvFileurl($lockToApplyId);
        $mediaUrl = $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA, true) . 'csv';
        $data = $this->csvProcessor->getData($path . str_replace($mediaUrl, '', $file));
        $headers = array_shift($data); // Extract the header row
        foreach ($data as $row) {

            $rowHeader = array_combine($headers, $row); // Combine headers with row data

            $sku = $rowHeader['sku'] ?? null;
            $qty = $rowHeader['qty'] ?? null;
            $model = $rowHeader['model'] ?? null;
            $discountPercentage = $rowHeader['discount_percentage'] ?? null;
            $fixedPrice = $rowHeader['fixedprice'] ?? null;
            if ($sku) {
                try {
                    $product = $this->productRepositoryInterface->get($sku);
                    if ($fixedPrice && $qty) {
                        $specialPrice = floatval($product->getSpecialPrice()) ?? null;
                        // Check if special price is valid (within date range)
                        $specialFromDate = $product->getSpecialFromDate();
                        $specialToDate = $product->getSpecialToDate();
                        $currentDate = date('Y-m-d') . " 00:00:00";
                        // Validate special price
                        if ($specialPrice > 0) {
                            if (($specialFromDate && $specialFromDate > $currentDate) || ($specialToDate && $specialToDate < $currentDate)) {
                                $specialPrice = null; // Special price is not valid
                            }
                        } else {
                            $specialPrice = null;
                        }
                        $tierPrice = $product->getTierPrice() ?? null;
                        $actualPrice = $product->getPrice();
                        $lowestPrice = null;
                        if (count($tierPrice) > 0) {
                            foreach ($tierPrice as $priceInfo) {
                                $price = floatval($priceInfo['price']); // Convert price to float for comparison

                                if ($lowestPrice === null || $price < $lowestPrice) {
                                    $lowestTierPrice = $price; // Update the lowest price
                                }
                            }
                        } else {
                            $lowestTierPrice = null;
                        }
                        // Filter out null values
                        $prices = array_filter([$actualPrice, $specialPrice, $lowestTierPrice], function ($price) {
                            return $price !== null;
                        });
                        // Get the minimum value among the prices
                        $minPrice = !empty($prices) ? min($prices) : null;
                        // Calculate special discount price
                        $specialDiscountPrice = $fixedPrice;
                        $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\ProductFactory::class);
                        $product = $productFactory->create()->load($product->getId());


                        if ($specialPrice === null || $specialPrice <= 0) {
                            $product->setSpecialDiscountPrice(null);
                        } else {
                            $product->setCustomAttribute('special_discount_price', $specialPrice);
                            // If $specialPrice is not null, set it as it is
                            // $product->setSpecialDiscountPrice($specialPrice);
                        }
                        $product->setSpecialPrice($specialDiscountPrice);
                        $product->setSpecialFromDate($currentDate);
                        $product->setSpecialToDate(null);
                    } elseif ($discountPercentage && $qty) {
                        $specialPrice = floatval($product->getSpecialPrice()) ?? null;
                        // Check if special price is valid (within date range)
                        $specialFromDate = $product->getSpecialFromDate();
                        $specialToDate = $product->getSpecialToDate();
                        $currentDate = date('Y-m-d') . " 00:00:00";
                        // Validate special price
                        if ($specialPrice > 0) {
                            if (($specialFromDate && $specialFromDate > $currentDate) || ($specialToDate && $specialToDate < $currentDate)) {
                                $specialPrice = null; // Special price is not valid
                            }
                        } else {
                            $specialPrice = null;
                        }
                        $tierPrice = $product->getTierPrice() ?? null;
                        $actualPrice = $product->getPrice();
                        $lowestPrice = null;
                        if (count($tierPrice) > 0) {
                            foreach ($tierPrice as $priceInfo) {
                                $price = floatval($priceInfo['price']); // Convert price to float for comparison

                                if ($lowestPrice === null || $price < $lowestPrice) {
                                    $lowestTierPrice = $price; // Update the lowest price
                                }
                            }
                        } else {
                            $lowestTierPrice = null;
                        }
                        // Filter out null values
                        $prices = array_filter([$actualPrice, $specialPrice, $lowestTierPrice], function ($price) {
                            return $price !== null;
                        });
                        // Get the minimum value among the prices
                        $minPrice = !empty($prices) ? min($prices) : null;
                        // Calculate special discount price
                        $specialDiscountPrice = $minPrice - ($minPrice * ($discountPercentage / 100));
                        $productFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(\Magento\Catalog\Model\ProductFactory::class);
                        $product = $productFactory->create()->load($product->getId());
                        if ($specialPrice === null || $specialPrice <= 0) {
                            $product->setSpecialDiscountPrice(null);
                        } else {
                            $product->setCustomAttribute('special_discount_price', $specialPrice);
                            // If $specialPrice is not null, set it as it is
                            // $product->setSpecialDiscountPrice($specialPrice);
                        }
                        $product->setSpecialPrice($specialDiscountPrice);
                        $product->setSpecialFromDate($currentDate);
                        $product->setSpecialToDate(null);
                    }
                    if (($discountPercentage && $qty) || ($fixedPrice && $qty)) {
                        // Get the stock item for the product
                        $stockItem = $this->stockRegistry->getStockItem($product->getId()); // Load stock of that product

                        // Check if the current quantity is less than the desired quantity
                        if ($stockItem->getQty() < $qty) {
                            // Update the quantity only if the stock quantity is less than the desired quantity
                            $stockItem->setQty($qty); // Set updated quantity
                            $stockItem->setIsInStock(1); // Set in stock status
                            $stockItem->setUseConfigNotifyStockQty(1); // Use config for notify stock quantity
                            $this->stockRegistry->updateStockItemBySku($product->getSku(), $stockItem); // Save stock item
                        }
                        // Update product quantity and stock status
                        $product->setStockData([
                            'is_in_stock' => 1  // Set stock status to "in stock"
                        ]);
                        if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED) {
                            $product->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                        }
                        // Update special price quantity if necessary
                        $product->setSpecialPriceQuantity($qty);
                        $this->assignSpecialOfferCategory($product);
                    }
                    // Set the model attribute if present
                    if ($model) {
                        $product->setCustomAttribute('product_model', $model);
                    }
                    $product->save();
                } catch (\Exception $e) {
                    // Log the error and continue processing the next product
                    continue;
                }
            }
        }
        $this->csvHelper->setJobInProcessToId($lockToApplyId, 0);
        $this->csvHelper->setCsvFileLockedToApplyToId($lockToApplyId, 0);
        $this->csvHelper->setCsvFileProcessToId($lockToApplyId, 1);
        return true;
    }

    public function assignSpecialOfferCategory($product)
    {
        $newCategoryId = 90;
        // Get the current categories of the product
        $currentCategories = $product->getCategoryIds();

        // Add the new category to the list if it's not already assigned
        if (!in_array($newCategoryId, $currentCategories)) {
            $currentCategories[] = $newCategoryId;
        }

        // Set the updated categories to the product
        $product->setCategoryIds($currentCategories);
    }
}
