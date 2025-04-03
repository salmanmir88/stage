<?php

namespace Coupon\NoOrderCoupon\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\RuleFactory;

class RestrictCouponUsage implements ObserverInterface
{
    protected $customerSession;
    protected $orderCollectionFactory;
    protected $messageManager;
    protected $couponFactory;
    protected $ruleFactory;

    public function __construct(
        CustomerSession $customerSession,
        OrderCollectionFactory $orderCollectionFactory,
        ManagerInterface $messageManager,
        CouponFactory $couponFactory,
        RuleFactory $ruleFactory,
        
    ) {
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->messageManager = $messageManager;
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
    }

    public function execute(Observer $observer)
    {
        $quote = $observer->getEvent()->getQuote();
        $couponCode = $quote->getCouponCode();
        
        if (!$quote) {
            return;
        }
        if ($couponCode) {
            // Load coupon rule
            $coupon = $this->couponFactory->create()->loadByCode($couponCode);
            $rule = $coupon->getRule();

            if ($rule && stripos($rule->getDescription(), 'new_customer') !== false) {
                // If "new_customer" is found in the description, enforce restriction
                $customerId = $this->customerSession->getCustomerId();

                if ($customerId) {
                    $orders = $this->orderCollectionFactory->create()
                        ->addFieldToFilter('customer_id', $customerId);

                    if ($orders->getSize() > 0) {
                        // Customer has previous orders, remove coupon
                        $quote->setCouponCode('')->save();
                        $this->messageManager->addErrorMessage(__('This coupon is only available for new customers.'));
                    }
                }
            }

            // Restrict all coupons if cart contains products with special_price_quantity
            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $specialPriceQuantity = $product->getData('special_price_quantity');

                if (!empty($specialPriceQuantity)) {
                    // Prevent coupon application
                    $quote->setCouponCode('')->save();
                    $this->messageManager->addErrorMessage(__('Coupon code is not applicable to products with special pricing.'));
                    return;
                }
            }
        }
    }
    
}
