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



namespace Mirasvit\Seo\Plugin\Adminhtml;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Mirasvit\Seo\Model\Config\ProductUrlTemplateConfig;
use Mirasvit\Seo\Service\TemplateEngineService;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroup;

/** @see \Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator */
class ProductUrlKeyPlugin
{
    /** @var \Magento\Catalog\Model\Product */
    private $product;

    private $templateEngineService;

    private $productUrlTemplateConfig;
    private $productRepository;
    private $searchBuilder;

    public function __construct(
        TemplateEngineService $templateEngineService,
        ProductUrlTemplateConfig $productUrlTemplateConfig,
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchBuilder
    ) {
        $this->templateEngineService    = $templateEngineService;
        $this->productUrlTemplateConfig = $productUrlTemplateConfig;
        $this->productRepository = $productRepository;
        $this->searchBuilder = $searchBuilder;
    }

    /**
     * @param object $subject
     * @param object $product
     *
     * @return array
     */
    public function beforeGetUrlKey($subject, $product)
    {
        $this->product = $product;

        return [$product];
    }

    /**
     * @param object $subject
     * @param string $urlKey
     *
     * @return string
     */
    public function afterGetUrlKey($subject, $urlKey)
    {
        if (!$this->product) {
            return $urlKey;
        }

        if($this->product->isObjectNew() === false) {
            if ($this->product->getUrlKey()) {
                return $urlKey;
            }
        } else {
            $storeId = (int)$this->product->getStoreId();

            $urlKeyTemplate = $this->productUrlTemplateConfig->getProductUrlKey($storeId);

            if (!$urlKeyTemplate) {
                return $urlKey;
            }
            $urlKey = $this->templateEngineService->render(
                $urlKeyTemplate,
                [
                    'product' => $this->product,
                    'store'   => $this->product->getStore(),
                ]
            );

            $urlKey = $this->product->formatUrlKey($urlKey);

            //search for unique urlKey
            for ($i = 1; $i < 100; $i++) {
                $searchCriteria = $this->searchBuilder
                    ->addFilter("url_key", $urlKey)
                    ->addFilter("store", $this->product->getStore())
                    ->setPageSize(1)
                    ->create();
                $products = $this->productRepository->getList($searchCriteria);
                if ($products->getTotalCount()) {
                    $revUrlKey = strrev($urlKey);
                    $revNum = (int)$revUrlKey;
                    $number = (int)strrev($revNum); //0 or some int
                    $suffix = "-".$number;
                    $pos = strpos($urlKey, $suffix);
                    if ($pos !== false &&  $pos == strlen($urlKey) - strlen($suffix)) {
                        $urlKey = substr($urlKey, 0, $pos);//url_key without suffix
                    }
                    $number++;
                    $urlKey .= "-".$number;
                    continue;
                }
                break;
            }
        }

        return $urlKey;
    }

}
