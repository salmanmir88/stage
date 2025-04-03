<?php
namespace Evince\MinicartTotal\Plugin\MiniCart;

class Cart
{
    protected $checkoutSession;
    protected $checkoutHelper;
    protected $quote;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
    }
    
    /**
     * Get active quote
     *
     * @return \Magento\Quote\Model\Quote
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    protected function getTaxAmount()
    {
        return $this->getQuote()->getShippingAddress()->getData('tax_amount');
    }

    protected function getOrderTotal()
    {
        return $this->getQuote()->getGrandTotal();
    }

    protected function getShippingPrice()
    {
        return $this->getQuote()->getShippingAddress()->getShippingAmount();
    }

    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['tax_amount_no_html'] = $this->getTaxAmount();
        $result['tax_amount'] = $this->checkoutHelper->formatPrice($this->getTaxAmount());
        $result['shipping_amount'] = $this->checkoutHelper->formatPrice($this->getShippingPrice());
        $result['order_total'] = $this->checkoutHelper->formatPrice($this->getOrderTotal());

        return $result;
    }
}