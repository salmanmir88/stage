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



namespace Mirasvit\SeoMarkup\Block\Og;

use Magento\Catalog\Block\Product\View;
use Magento\Catalog\Helper\Output as OutputHelper;
use Magento\Catalog\Pricing\Price\FinalPrice;
use Magento\Directory\Block\Currency;
use Magento\Framework\View\Element\Template;
use Mirasvit\Seo\Api\Service\StateServiceInterface;

class Product extends AbstractBlock
{
    private $view;

    private $currency;

    private $outputHelper;

    private $stateService;

    public function __construct(
        View $view,
        Currency $currency,
        OutputHelper $outputHelper,
        StateServiceInterface $stateService,
        Template\Context $context
    ) {
        $this->view         = $view;
        $this->currency     = $currency;
        $this->outputHelper = $outputHelper;
        $this->stateService = $stateService;

        parent::__construct($context);
    }

    protected function getMeta()
    {
        $product = $this->stateService->getProduct();

        if (!$product) {
            return false;
        }

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->_storeManager->getStore();

        $priceAmount = $product->getPriceInfo()
            ->getPrice(FinalPrice::PRICE_CODE)
            ->getAmount();

        $meta = [
            'og:type'                => 'product',
            'og:url'                 => $this->_urlBuilder->escape($product->getUrl()),
            'og:title'               => $this->pageConfig->getTitle()->get(),
            'og:description'         => $this->outputHelper->productAttribute(
                $product,
                $product->getData('short_description'),
                'og:short_description'
            ),
            'og:image'               => $this->view->getImage($product, 'product_base_image')->getImageUrl(),
            'og:site_name'           => $store->getFrontendName(),
            'product:price:amount'   => $priceAmount,
            'product:price:currency' => $this->currency->getCurrentCurrencyCode(),
        ];

        return $meta;
    }
}
