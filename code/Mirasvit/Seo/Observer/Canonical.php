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



namespace Mirasvit\Seo\Observer;

use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Seo\Api\Service\StateServiceInterface;
use Mirasvit\Seo\Model\Config as Config;

/**
 * @SuppressWarnings(PHPMD)
 */
class Canonical implements ObserverInterface
{
    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Bundle\Model\Product\TypeFactory
     */
    protected $productTypeFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    protected $categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $seoData;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable
     */
    protected $productTypeConfigurable;

    /**
     * @var \Magento\Bundle\Model\Product\Type
     */
    protected $productTypeBundle;

    /**
     * @var \Magento\GroupedProduct\Model\Product\Type\Grouped
     */
    protected $productTypeGrouped;

    /**
     * @var \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection
     */
    protected $urlRewrite;

    /**
     * @var \Mirasvit\Seo\Helper\UrlPrepare
     */
    protected $urlPrepare;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var StateServiceInterface
     */
    private $stateService;

    /**
     * @var \Mirasvit\Seo\Api\Service\CanonicalRewrite\CanonicalRewriteServiceInterface
     */
    private $canonicalRewriteService;

    public function __construct(
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Bundle\Model\Product\TypeFactory $productTypeFactory,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable $productTypeConfigurable,
        \Magento\Bundle\Model\Product\Type $productTypeBundle,
        \Magento\GroupedProduct\Model\Product\Type\Grouped $productTypeGrouped,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Seo\Helper\Data $seoData,
        \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $urlRewrite,
        \Mirasvit\Seo\Helper\UrlPrepare $urlPrepare,
        \Mirasvit\Seo\Api\Service\CanonicalRewrite\CanonicalRewriteServiceInterface $canonicalRewriteService,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        StateServiceInterface $stateService
    ) {
        $this->config                    = $config;
        $this->productTypeFactory        = $productTypeFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->productCollectionFactory  = $productCollectionFactory;
        $this->productTypeConfigurable   = $productTypeConfigurable;
        $this->productTypeBundle         = $productTypeBundle;
        $this->productTypeGrouped        = $productTypeGrouped;
        $this->context                   = $context;
        $this->registry                  = $registry;
        $this->seoData                   = $seoData;
        $this->storeManager              = $context->getStoreManager();
        $this->request                   = $context->getRequest();
        $this->urlRewrite                = $urlRewrite;
        $this->urlPrepare                = $urlPrepare;
        $this->canonicalRewriteService   = $canonicalRewriteService;
        $this->productRepository         = $productRepository;
        $this->stateService              = $stateService;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->setupCanonicalUrl();
    }

    /**
     * @return bool
     */
    public function setupCanonicalUrl()
    {
        if ($this->seoData->isIgnoredActions()
            && !$this->seoData->cancelIgnoredActions()) {
            return false;
        }

        if ($canonicalUrl = $this->getCanonicalUrl()) {
            $this->addLinkCanonical($canonicalUrl);
        }
    }

    /**
     * @return $this|mixed|string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity) 
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function getCanonicalUrl()
    {
        if (!$this->config->isAddCanonicalUrl() || $this->isIgnoredCanonical()) {
            return false;
        }

        if ($canonicalRewrite = $this->getCanonicalRewrite()) {
            return $canonicalRewrite;
        }

        $productActions = [
            'catalog_product_view',
            'review_product_list',
            'review_product_view',
            'productquestions_show_index',
        ];

        $productCanonicalStoreId = false;
        $useCrossDomain          = true;

        if (in_array($this->seoData->getFullActionCode(), $productActions)) {
            $product = $this->registry->registry('current_product');

            if (!$product) {
                return;
            }

            $currentProductId    = $product->getId();
            $associatedProductId = $this->getAssociatedProductId($product);
            $productId           = ($associatedProductId) ? $associatedProductId : $product->getId();

            $productCanonicalStoreId       = $product->getSeoCanonicalStoreId(); //canonical store id for current product
            $canonicalUrlForCurrentProduct = trim($product->getSeoCanonicalUrl());

            $collection = $this->productCollectionFactory->create()
                ->addFieldToFilter('entity_id', $productId)
                ->addStoreFilter()
                ->addUrlRewrite();

            $collection->setFlag('has_stock_status_filter');

            $product      = $collection->getFirstItem();
            $canonicalUrl = $product->getProductUrl();

            if ($this->config->isAddLongestCanonicalProductUrl()
                && $this->config->isProductLongUrlEnabled($this->storeManager->getStore()->getId())
            ) {
                $canonicalUrl = $this->getLongestProductUrl($product, $canonicalUrl);
            }

            if ($canonicalUrlForCurrentProduct) {
                if (strpos($canonicalUrlForCurrentProduct, 'http://') !== false
                    || strpos($canonicalUrlForCurrentProduct, 'https://') !== false
                ) {
                    $canonicalUrl   = $canonicalUrlForCurrentProduct;
                    $useCrossDomain = false;
                } else {
                    $canonicalUrlForCurrentProduct = (substr(
                            $canonicalUrlForCurrentProduct,
                            0,
                            1
                        ) == '/') ? substr($canonicalUrlForCurrentProduct, 1) : $canonicalUrlForCurrentProduct;
                    $canonicalUrl                  = $this->context->getUrlBuilder()->getBaseUrl() . $canonicalUrlForCurrentProduct;
                }
            }
            $productLoaded = $this->productRepository->getById(
                $currentProductId,
                false,
                $this->storeManager->getStore()->getId()
            );

            //use custom canonical from products
            if ($productLoaded->getMSeoCanonical()) {
                $canonicalUrl = trim($productLoaded->getMSeoCanonical());
                if (strpos($canonicalUrl, '://') === false) {
                    $canonicalUrl = $this->storeManager->getStore()->getBaseUrl() . ltrim($canonicalUrl, '/');
                }
            }
        } elseif ($this->seoData->getFullActionCode() == 'catalog_category_view') {
            $category = $this->registry->registry('current_category');
            if (!$category) {
                return;
            }
            $canonicalUrl = $category->getUrl();
        } else {
            $canonicalUrl              = $this->seoData->getBaseUri();
            $preparedCanonicalUrlParam = ($this->config->isAddStoreCodeToUrlsEnabled()
                && $this->stateService->isHomePage()) ? '' : ltrim($canonicalUrl, '/');
            $canonicalUrl              = $this->context->getUrlBuilder()->getUrl('', ['_direct' => $preparedCanonicalUrlParam]);
            $canonicalUrl              = strtok($canonicalUrl, '?');
        }

        if ($this->config->getCanonicalStoreWithoutStoreCode($this->storeManager->getStore()->getId())) {
            $storeCode    = $this->storeManager->getStore()->getCode();
            $canonicalUrl = str_replace('/' . $storeCode . '/', '/', $canonicalUrl);
            //setup crossdomian URL if this option is enabled
        } elseif ((($crossDomainStore = $this->config->getCrossDomainStore($this->storeManager->getStore()->getId()))
                || $productCanonicalStoreId)
            && $useCrossDomain) {
            if ($productCanonicalStoreId) {
                $crossDomainStore = $productCanonicalStoreId;
            }
            $mainBaseUrl    = $this->storeManager->getStore($crossDomainStore)->getBaseUrl();
            $currentBaseUrl = $this->storeManager->getStore()->getBaseUrl();
            $canonicalUrl   = str_replace($currentBaseUrl, $mainBaseUrl, $canonicalUrl);

            $mainSecureBaseUrl = $this->storeManager->getStore($crossDomainStore)
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, true);

            if ($this->storeManager->getStore()->isCurrentlySecure()
                || ($this->config->isPreferCrossDomainHttps()
                    && strpos($mainSecureBaseUrl, 'https://') !== false)) {
                $canonicalUrl = str_replace('http://', 'https://', $canonicalUrl);
            }
        }

        $canonicalUrl = $this->urlPrepare->deleteDoubleSlash($canonicalUrl);

        $page = (int)$this->request->getParam('p');
        if ($page > 1 && $this->config->isPaginatedCanonical()) {
            $canonicalUrl .= "?p=$page";
        }

        $canonicalUrl = $this->getPreparedTrailingCanonical($canonicalUrl);

        return $canonicalUrl;
    }

    /**
     * Check if canonical is ignored.
     * @return bool
     */
    public function isIgnoredCanonical()
    {
        $isIgnored = false;

        if (!$this->seoData->getFullActionCode() || $this->seoData->getFullActionCode() == '__') {
            return true;
        }

        foreach ($this->config->getCanonicalUrlIgnorePages() as $page) {
            if ($this->seoData->checkPattern($this->seoData->getFullActionCode(), $page)
                || $this->seoData->checkPattern($this->seoData->getBaseUri(), $page)) {
                $isIgnored = true;
            }
        }

        return $isIgnored;
    }

    /**
     * @return string|bool
     */
    public function getCanonicalRewrite()
    {
        if ($canonicalRewriteRule = $this->canonicalRewriteService->getCanonicalRewriteRule()) {
            return $canonicalRewriteRule->getData('canonical');
        }

        return false;
    }

    /**
     * Get associated product Id
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return bool|int
     */
    protected function getAssociatedProductId($product)
    {
        if ($product->getTypeId() != \Magento\Catalog\Model\Product\Type::TYPE_SIMPLE) {
            return false;
        }

        $associatedProductId = false;

        if ($this->config->getAssociatedCanonicalConfigurableProduct()
            && ($parentConfigurableProductIds = $this
                ->productTypeConfigurable
                ->getParentIdsByChild($product->getId())
            )
            && isset($parentConfigurableProductIds[0])
            && $this->isProductEnabled($parentConfigurableProductIds[0])) {
            $associatedProductId = $parentConfigurableProductIds[0];
        }

        if (!$associatedProductId && $this->config->getAssociatedCanonicalGroupedProduct()
            && ($parentGroupedProductIds = $this
                ->productTypeGrouped
                ->getParentIdsByChild($product->getId())
            )
            && isset($parentGroupedProductIds[0])
            && $this->isProductEnabled($parentGroupedProductIds[0])) {
            $associatedProductId = $parentGroupedProductIds[0];
        }

        if (!$associatedProductId && $this->config->getAssociatedCanonicalBundleProduct()
            && ($parentBundleProductIds = $this
                ->productTypeBundle
                ->getParentIdsByChild($product->getId())
            )
            && isset($parentBundleProductIds[0])
            && $this->isProductEnabled($parentBundleProductIds[0])) {
            $associatedProductId = $parentBundleProductIds[0];
        }

        return $associatedProductId;
    }

    /**
     * return bool
     *
     * @param string $id
     *
     * @return bool
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function isProductEnabled($id)
    {
        $product = $this->productRepository->getById(
            $id,
            false,
            $this->storeManager->getStore()->getId()
        );

        if ($product->getStatus() == \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED) {
            return true;
        }

        return false;
    }

    /**
     * Get longest product url
     *
     * @param object $product
     * @param string $canonicalUrl
     *
     * @return string
     */
    protected function getLongestProductUrl($product, $canonicalUrl)
    {
        $rewriteData = $this->urlRewrite->addFieldToFilter('entity_type', 'product')
            ->addFieldToFilter('redirect_type', 0)
            ->addFieldToFilter('store_id', $this->storeManager->getStore()->getId())
            ->addFieldToFilter('entity_id', $product->getId());

        if ($rewriteData && $rewriteData->getSize() > 1) {
            $urlPath = [];
            foreach ($rewriteData as $rewrite) {
                $requestPath             = $rewrite->getRequestPath();
                $requestPathExploded     = explode('/', $requestPath);
                $categoryCount           = count($requestPathExploded);
                $urlPath[$categoryCount] = $requestPath;
            }

            if ($urlPath) {
                $canonicalUrl = $this->storeManager->getStore()->getBaseUrl() . $urlPath[max(array_keys($urlPath))];
            }
        }

        return $canonicalUrl;
    }

    /**
     * Get Canonical with prepared Trailing slash (depending on Trailing slash config)
     *
     * @param string $canonicalUrl
     *
     * @return string
     */
    protected function getPreparedTrailingCanonical($canonicalUrl)
    {
        $extension = substr(strrchr($canonicalUrl, '.'), 1);

        if ($this->config->getTrailingSlash() == Config::TRAILING_SLASH
            && substr($canonicalUrl, -1) != '/'
            && strpos($canonicalUrl, '?') === false
            && !in_array($extension, ['html', 'htm'])) {
            $canonicalUrl = $canonicalUrl . '/';
        } elseif ($this->config->getTrailingSlash() == Config::NO_TRAILING_SLASH
            && substr($canonicalUrl, -1) == '/') {
            if ($this->checkHomePageCanonical($canonicalUrl)) {
                return $canonicalUrl;
            } else {
                $canonicalUrl = substr($canonicalUrl, 0, -1);
            }
        }

        return $canonicalUrl;
    }

    /**
     * @param string $canonicalUrl
     *
     * @return bool
     */

    protected function checkHomePageCanonical($canonicalUrl)
    {
        if ($this->stateService->isHomePage()
            && $this->config->isAddStoreCodeToUrlsEnabled()
            && $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB)
            . $this->storeManager->getStore()->getCode()
            . '/' == $this->context->getUrlBuilder()->getCurrentUrl()
            && $this->context->getUrlBuilder()->getCurrentUrl() == $canonicalUrl) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Create canonical.
     *
     * @param string $canonicalUrl
     *
     * @return void
     */
    public function addLinkCanonical($canonicalUrl)
    {
        $pageConfig = $this->context->getPageConfig();
        $type       = 'canonical';
        $pageConfig->addRemotePageAsset(
            htmlentities($canonicalUrl),
            $type,
            ['attributes' => ['rel' => $type]]
        );
    }
}
