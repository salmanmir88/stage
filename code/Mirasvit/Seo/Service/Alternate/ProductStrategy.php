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



namespace Mirasvit\Seo\Service\Alternate;

use Magento\UrlRewrite\Service\V1\Data\UrlRewrite as UrlRewrite;
use Magento\Catalog\Model\Product\Visibility;
use Mirasvit\Seo\Api\Service\Alternate\UrlInterface;
use Magento\Framework\Registry;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\Framework\App\Request\Http;
use Magento\Catalog\Api\ProductRepositoryInterface;


class ProductStrategy implements \Mirasvit\Seo\Api\Service\Alternate\StrategyInterface
{
    /**
     * @var \Mirasvit\Seo\Api\Service\Alternate\UrlInterface
     */
    protected $url;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\UrlRewrite\Model\UrlFinderInterface
     */
    protected $urlFinder;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @param \Mirasvit\Seo\Api\Service\Alternate\UrlInterface $url
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\UrlRewrite\Model\UrlFinderInterface $urlFinder
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        UrlInterface $url,
        Registry $registry,
        UrlFinderInterface $urlFinder,
        Http $request,
        ProductRepositoryInterface $productRepository
    ) {
        $this->url                  = $url;
        $this->registry             = $registry;
        $this->urlFinder            = $urlFinder;
        $this->urlFinder            = $urlFinder;
        $this->request              = $request;
        $this->productRepository    = $productRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreUrls()
    {
        $storeUrls = $this->url->getStoresCurrentUrl();
        $storeUrls = $this->getAlternateUrl($storeUrls);

        return $storeUrls;
    }

    /**
     * {@inheritdoc}
     */
    public function getAlternateUrl($storeUrls)
    {
        $product = $this->registry->registry('current_product');
        $productId = $product->getId();

        foreach ($this->url->getStores() as $storeId => $store) {
            $product = $this->productRepository->getById($product->getId(),false,$storeId);

            if ($product->getData('visibility') == Visibility::VISIBILITY_NOT_VISIBLE) {
                unset($storeUrls[$storeId]);
                continue;
            }

            $idPath = $this->request->getPathInfo();

            if ($idPath && strpos($idPath, $productId) !== false) {
                $rewriteObject = $this->urlFinder->findOneByData([
                    UrlRewrite::TARGET_PATH => trim($idPath, '/'),
                    UrlRewrite::STORE_ID => $storeId,
                ]);

                if ($rewriteObject && ($requestPath = $rewriteObject->getRequestPath())) {
                    $storeUrls[$storeId] = $store->getBaseUrl().$requestPath.$this->url->getUrlAddition($store);
                }
            }
        }

        return $storeUrls;
    }


}