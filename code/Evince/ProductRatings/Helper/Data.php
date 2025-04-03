<?php

namespace Evince\ProductRatings\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $storeManager;
    protected $reviewFactory;

    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Review\Model\ReviewFactory $reviewFactory
    ) {
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
    }

    public function getRatingSummary($product) {
        $this->reviewFactory->create()->getEntitySummary($product, $this->storeManager->getStore()->getId());
        $ratingSummary = $product->getRatingSummary()->getRatingSummary();
        return $ratingSummary;
    }

}
