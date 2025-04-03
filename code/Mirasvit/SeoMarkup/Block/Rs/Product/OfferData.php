<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\SeoMarkup\Block\Rs\Product;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Shipping\Model\Config as ShippingConfig;
use Mirasvit\Seo\Api\Service\TemplateEngineServiceInterface;
use Mirasvit\SeoMarkup\Model\Config\ProductConfig;

class OfferData
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    /**
     * @var FormatInterface
     */
    private $formatInterface;

    /**
     * @var \Magento\Store\Model\Store
     */
    private $store;

    /**
     * @var ProductConfig
     */
    private $productConfig;

    /**
     * @var TemplateEngineServiceInterface
     */
    private $templateEngineService;

    /**
     * @var PaymentConfig
     */
    private $paymentConfig;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var ShippingConfig
     */
    private $shippingConfig;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * OfferData constructor.
     *
     * @param ProductConfig                  $productConfig
     * @param TemplateEngineServiceInterface $templateEngineService
     * @param PaymentConfig                  $paymentConfig
     * @param ScopeConfigInterface           $scopeConfig
     * @param ShippingConfig                 $shippingConfig
     * @param LayoutInterface                $layout
     * @param FormatInterface                $formatInterface
     */
    public function __construct(
        ProductConfig $productConfig,
        TemplateEngineServiceInterface $templateEngineService,
        PaymentConfig $paymentConfig,
        ScopeConfigInterface $scopeConfig,
        ShippingConfig $shippingConfig,
        LayoutInterface $layout,
        FormatInterface $formatInterface
    ) {
        $this->productConfig         = $productConfig;
        $this->templateEngineService = $templateEngineService;
        $this->paymentConfig         = $paymentConfig;
        $this->scopeConfig           = $scopeConfig;
        $this->shippingConfig        = $shippingConfig;
        $this->layout                = $layout;
        $this->formatInterface       = $formatInterface;
    }

    /**
     * @param object $product
     * @param object $store
     * @param bool   $dry
     *
     * @return array|false
     */
    public function getData($product, $store, $dry = false)
    {
        $this->product = $dry ? $product : $product->load($product->getId());
        $this->store   = $store;

        $currencyCode  = $this->store->getCurrentCurrencyCode();
        $specialToDate = $this->templateEngineService->render('[product_special_to_date]', ['product' => $product]);
        $finalPrice    = $this->getPrice($this->product);
        $finalPrice    = preg_replace('/.*special-price.*>/U', '', $finalPrice);
        $finalPrice    = strip_tags(html_entity_decode($finalPrice));
        preg_match_all('/[0-9\.\,]+/', $finalPrice, $matches);

        if (isset($matches[0][0])) {
            $finalPrice = $matches[0][0];
        } elseif (!empty($product->getFinalPrice())) {
            $finalPrice = $product->getFinalPrice();
        } else {
            return false;
        }

        $finalPrice = $this->formatInterface->getNumber($finalPrice);
        $values     = [
            '@type'                   => 'Offer',
            'url'                     => $this->product->getVisibility() != 1 ? $this->product->getProductUrl() : false,
            'price'                   => number_format($finalPrice, 2, '.', ''),
            'priceCurrency'           => $currencyCode,
            'priceValidUntil'         => empty($specialToDate) ? '2030-01-01' : $specialToDate,
            'availability'            => $this->getOfferAvailability(),
            'itemCondition'           => $this->getOfferItemCondition(),
            'acceptedPaymentMethod'   => $this->getOfferAcceptedPaymentMethods(),
            'availableDeliveryMethod' => $this->getOfferAvailableDeliveryMethods(),
            'sku'                     => $this->product->getSku(),
            'gtin'                    => $this->getGtin()
        ];

        $data = [];
        foreach ($values as $key => $value) {
            if ($value) {
                $data[$key] = $value;
            }
        }

        return $data;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return string
     */
    public function getPrice($product)
    {
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        $price = '';
        if ($priceRender) {
            /** @var mixed $priceRender */
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                [
                    'display_minimal_price'  => true,
                    'use_link_for_as_low_as' => true,
                    'zone'                   => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
                ]
            );
        }

        return $price;
    }

    /**
     * @return array|false
     */
    protected function getOfferAvailableDeliveryMethods()
    {
        if (!$this->productConfig->isAvailableDeliveryMethodEnabled()) {
            return false;
        }

        if ($activeDeliveryMethods = $this->getActiveDeliveryMethods()) {
            return $activeDeliveryMethods;
        }

        return false;
    }

    /**
     * @return string|false
     */
    private function getOfferAvailability()
    {
        if (!$this->productConfig->isAvailabilityEnabled()) {
            return false;
        }

        $productAvailability = method_exists($this->product, 'isAvailable')
            ? $this->product->isAvailable()
            : $this->product->isInStock();

        if ($productAvailability) {
            return "http://schema.org/InStock";
        } else {
            return "http://schema.org/OutOfStock";
        }
    }

    /**
     * @return string|false
     */
    private function getOfferItemCondition()
    {
        $conditionType = $this->productConfig->getItemConditionType();

        if (!$conditionType) {
            return false;
        }

        if ($conditionType == ProductConfig::ITEM_CONDITION_NEW_ALL) {
            return "http://schema.org/NewCondition";
        } elseif ($conditionType == ProductConfig::ITEM_CONDITION_MANUAL) {
            $attribute      = $this->productConfig->getItemConditionAttribute();
            $conditionValue = $this->templateEngineService->render("[product_$attribute]");

            if (!$conditionValue) {
                return false;
            }

            switch ($conditionValue) {
                case $this->productConfig->getItemConditionAttributeValueNew():
                    return "http://schema.org/NewCondition";

                case $this->productConfig->getItemConditionAttributeValueUsed():
                    return "http://schema.org/UsedCondition";

                case $this->productConfig->getItemConditionAttributeValueRefurbished():
                    return "http://schema.org/RefurbishedCondition";

                case $this->productConfig->getItemConditionAttributeValueDamaged():
                    return "http://schema.org/DamagedCondition";
            }
        }

        return false;
    }

    /**
     * @return array|false
     */
    private function getOfferAcceptedPaymentMethods()
    {
        if (!$this->productConfig->isAcceptedPaymentMethodEnabled()) {
            return false;
        }

        if ($activePaymentMethods = $this->getActivePaymentMethods()) {
            return $activePaymentMethods;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getActivePaymentMethods()
    {
        $payments = $this->paymentConfig->getActiveMethods();
        $methods  = [];
        foreach (array_keys($payments) as $paymentCode) {
            if (strpos($paymentCode, 'paypal') !== false) {
                $methods[] = 'http://purl.org/goodrelations/v1#PayPal';
            }

            if (strpos($paymentCode, 'googlecheckout') !== false) {
                $methods[] = 'http://purl.org/goodrelations/v1#GoogleCheckout';
            }

            if (strpos($paymentCode, 'cash') !== false) {
                $methods[] = 'http://purl.org/goodrelations/v1#Cash';
            }

            if ($paymentCode == 'ccsave') {
                if ($existingMethods = $this->getActivePaymentCCTypes()) {
                    $methods = array_merge($methods, $existingMethods);
                }
            }
        }

        return array_unique($methods);
    }

    /**
     * @return array|bool
     */
    private function getActivePaymentCCTypes()
    {
        $methods    = [];
        $allMethods = [
            'AE'  => 'AmericanExpress',
            'VI'  => 'VISA',
            'MC'  => 'MasterCard',
            'DI'  => 'Discover',
            'JCB' => 'JCB',
        ];

        $ccTypes = $this->scopeConfig->getValue(
            'payment/ccsave/cctypes',
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            $this->store
        );

        if ($ccTypes) {
            $list = explode(',', $ccTypes);

            foreach ($list as $value) {
                if (isset($allMethods[$value])) {
                    $methods[] = 'http://purl.org/goodrelations/v1#' . $allMethods[$value];
                }
            }

            return $methods;
        }

        return false;
    }

    /**
     * @return array
     */
    private function getActiveDeliveryMethods()
    {
        $methods = [];

        $allMethods = [
            'flatrate'     => 'DeliveryModeFreight',
            'freeshipping' => 'DeliveryModeFreight',
            'tablerate'    => 'DeliveryModeFreight',
            'dhl'          => 'DHL',
            'fedex'        => 'FederalExpress',
            'ups'          => 'UPS',
            'usps'         => 'DeliveryModeMail',
            'dhlint'       => 'DHL',
        ];

        $deliveryMethods = $this->shippingConfig->getActiveCarriers();
        foreach (array_keys($deliveryMethods) as $code) {
            if (isset($allMethods[$code])) {
                $methods[] = 'http://purl.org/goodrelations/v1#' . $allMethods[$code];
            }
        }

        return array_unique($methods);
    }

    /**
     * @return string|null
     */
    private function getGtin()
    {
        return $this->product->getData($this->productConfig->getGtin8Attribute());
    }
}
