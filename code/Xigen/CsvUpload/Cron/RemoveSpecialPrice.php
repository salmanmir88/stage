<?php

namespace Xigen\CsvUpload\Cron;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Xigen\CsvUpload\Helper\Csv;
use Magento\Framework\Notification\NotifierInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\CategoryLinkRepository;
use Magento\Catalog\Model\ProductFactory;

class RemoveSpecialPrice
{
    protected $productCollectionFactory;
    protected $csvHelper;
    protected $notifier;
    protected $productRepository;
    protected $categoryLinkRepository;
    protected $productFactory;

    public function __construct(
        ProductCollectionFactory $productCollectionFactory,
        Csv $csvHelper,
        ProductRepositoryInterface $productRepository,
        CategoryLinkRepository $categoryLinkRepository,
        ProductFactory $productFactory,
        NotifierInterface $notifier
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->csvHelper = $csvHelper;
        $this->notifier = $notifier;
        $this->productRepository = $productRepository;
        $this->categoryLinkRepository = $categoryLinkRepository;
        $this->productFactory = $productFactory;
    }

    public function execute()
    {
        $isLockedToRemove = $this->csvHelper->isAnyCsvFileLockedToRemove();
        $lockId = $this->csvHelper->getLockedToRemoveCsvFileId();
        $inProcess = $this->csvHelper->isAnyJobInProcess();
        if ($inProcess) {
            return true;
        }
        if ($isLockedToRemove && $lockId) {
            $collection = $this->productCollectionFactory->create()->addAttributeToFilter('special_price_quantity', ['notnull' => true]);
            $this->csvHelper->setJobInProcessToId($lockId, 1);
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
            }

            if ($skus) {
                $this->categoryLinkRepository->deleteBySkus($categoryId, $skus);
            }
            $this->csvHelper->setJobInProcessToId($lockId, 0);
            $this->csvHelper->setRemoveFileLockedToId($lockId, 0);
            $this->csvHelper->resetValueForAppliedFiles();
            $this->notifier->addNotice(
                __('Special Price Rule Removed Successfully for ' . $lockId),
                __('')
            );
        } else {
            return true;
        }
    }
}
