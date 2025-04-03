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



namespace Mirasvit\Seo\Service\UrlTemplate;

use Magento\Catalog\Model\ProductFactory as ProductFactory;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as EavAttribute;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Mirasvit\Seo\Api\Data\SuffixInterface as Suffix;
use Mirasvit\Seo\Helper\Version as VersionHelper;
use Mirasvit\Seo\Model\Config\ProductUrlTemplateConfig;
use Mirasvit\Seo\Service\TemplateEngineService;

class ProductUrlTemplateService
{
    /**
     * @var Suffix
     */
    private $productSuffix;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductUrlTemplateConfig
     */
    private $productUrlTemplateConfig;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var EavAttribute
     */
    private $eavAttribute;

    /**
     * @var VersionHelper
     */
    private $versionHelper;

    /**
     * @var productFactory
     */
    private $productFactory;

    /**
     * @var TemplateEngineService
     */
    private $templateEngineService;

    /**
     * ProductUrlTemplateService constructor.
     * @param Suffix $productSuffix
     * @param ProductUrlTemplateConfig $urlTemplateConfig
     * @param UrlPersistInterface $urlPersist
     * @param ResourceConnection $resource
     * @param EavAttribute $eavAttribute
     * @param VersionHelper $versionHelper
     * @param productFactory $productFactory
     * @param TemplateEngineService $templateEngineService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Suffix $productSuffix,
        ProductUrlTemplateConfig $urlTemplateConfig,
        UrlPersistInterface $urlPersist,
        ResourceConnection $resource,
        EavAttribute $eavAttribute,
        VersionHelper $versionHelper,
        productFactory $productFactory,
        TemplateEngineService $templateEngineService,
        StoreManagerInterface $storeManager
    ) {
        $this->productSuffix            = $productSuffix;
        $this->productUrlTemplateConfig = $urlTemplateConfig;
        $this->urlPersist               = $urlPersist;
        $this->resource                 = $resource;
        $this->eavAttribute             = $eavAttribute;
        $this->versionHelper            = $versionHelper;
        $this->productFactory = $productFactory;
        $this->templateEngineService    = $templateEngineService;
        $this->storeManager             = $storeManager;
    }

    /**
     * @return array
     */
    public function getUrlKeyTemplates()
    {
        $urlTemplate = [];

        /** @var \Magento\Store\Model\Store $store */
        foreach ($this->storeManager->getStores() as $store) {
            $productUrlKey = $this->productUrlTemplateConfig->getProductUrlKey($store->getId());

            if ($store->getIsActive() && $productUrlKey && strpos($productUrlKey, '[') !== false) {
                $urlTemplate[$store->getId()] = $productUrlKey;
            } else {
                $urlTemplate[$store->getId()] = false;
            }
        }

        return $urlTemplate;
    }

    /**
     * @param string $message
     * @param string $pid
     * @param string $sid
     * @param string $key
     * @return string
     */
    public function message($message, $pid, $sid, $key)
    {
        return $message . ':: Product ID: ' . $pid . ' Store ID: ' . $sid . ' Url Key: ' . $key;
    }

    /**
     * @param \Magento\UrlRewrite\Model\ResourceModel\UrlRewriteCollection $urlRewriteCollection
     * @param string $urlKeyTemplate
     * @param mixed $dryRun
     * @return \Generator
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function processUrlRewriteCollection($urlRewriteCollection, $urlKeyTemplate, $dryRun)
    {

        /** @var \Magento\UrlRewrite\Model\UrlRewrite $rewrite */
        foreach ($urlRewriteCollection as $rewrite) {
            $storeId = $rewrite->getStoreId();

            if (!isset($urlKeyTemplate[$storeId]) || !$urlKeyTemplate[$storeId]) {
                continue;
            }

            $productId = $rewrite->getEntityId();
            $product   = $this->getProduct($productId, $storeId);

            $store     = $this->storeManager->getStore($storeId);
            $urlKey = $this->getUrlKey($product, $store, $urlKeyTemplate[$storeId]);

            if ($urlKey === $product->getUrlKey()) {
                yield $this->message("Already used url key", $productId, $storeId, $urlKey);

                continue;
            }

            $isUnique = $this->isUniqueUrlKey($urlKey, $productId, $storeId);

            /**
             * We set url_path, because
             * in Magento\CatalogUrlRewrite\Model\ProductUrlPathGenerator
             * public function getUrlPath($product, $category = null) use url_path to generate urls
             */
            if (!$urlKey) {
                yield $this->message("Empty url key", $productId, $storeId, $urlKey);
            } elseif (!$isUnique) {
                yield $this->message("Duplicate used url key", $productId, $storeId, $urlKey);
            } else {
                if ($storeId && $product->getData('url_path')) {
                    $product->setData('url_path', $urlKey);
                }

                $product->setData('save_rewrites_history', true);

                if (!$dryRun) {
                    $this->applyUrlKey($urlKey, $product);
                }

                yield $this->message("Updated url key", $productId, $storeId, $urlKey);
            }
        }
    }

    /**
     * @param string $urlKey
     * @param int    $productId
     * @param int    $storeId
     *
     * @return bool
     */
    private function isUniqueUrlKey($urlKey, $productId, $storeId)
    {
        $isUniqueUrlKey = true;
        $url            = $urlKey;

        if ($suffix = $this->productSuffix->getProductUrlSuffix($storeId)) {
            $url = $urlKey . $suffix;
        }

        $connection = $this->resource->getConnection();
        $table      = $this->resource->getTableName('url_rewrite');

        $select = $connection->select()
            ->from(['url_rewrite' => $table], '*')
            ->where('url_rewrite.entity_type = ?', 'product')
            ->where('url_rewrite.store_id = ?', $storeId)
            ->where('url_rewrite.request_path = ?', $url)
            ->where('url_rewrite.entity_id <> ?', $productId);

        $selectData = $connection->fetchAll($select);

        if ($selectData) {
            $isUniqueUrlKey = false;
        }

        return $isUniqueUrlKey;
    }

    /**
     * @param string $urlKey
     * @param object $product
     *
     * @return void
     */
    private function applyUrlKey($urlKey, $product)
    {
        $product->setUrlKey($urlKey);

        if ($product->isVisibleInSiteVisibility()) {
            //setup::install compatibility
            $productUrlRewriteGenerator = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::class
            );

            $this->urlPersist->replace($productUrlRewriteGenerator->generate($product));

            $this->updateEntityUrlKey($urlKey, $product);
        }
    }

    /**
     * @param string $urlKey
     * @param object $product
     *
     * @return void
     */
    private function updateEntityUrlKey($urlKey, $product)
    {
        $connection = $this->resource->getConnection();

        $productId   = $product->getId();
        $attributeId = $this->eavAttribute->getIdByCode('catalog_product', 'url_key');
        $storeId     = $product->getStoreId();

        $select = $connection->select()
            ->from(['eav' => $this->resource->getTableName('catalog_product_entity_varchar')], '*')
            ->where('eav.attribute_id = ?', $attributeId)
            ->where('eav.store_id = ?', $storeId);

        if ($this->versionHelper->isEe()) {
            $select->join(
                ['e' => $this->resource->getTableName('catalog_product_entity')],
                'eav.row_id = e.row_id',
                ['e.entity_id']
            )->where('e.entity_id = ?', $productId);
        } else {
            $select->where('eav.entity_id = ?', $productId);
        }

        $row = $connection->fetchRow($select);

        if ($row) {
            $connection->update(
                $this->resource->getTableName('catalog_product_entity_varchar'),
                ['value' => $urlKey],
                ['value_id = ?' => $row['value_id']]
            );
        } else {
            $bind = [
                'attribute_id' => $attributeId,
                'store_id'     => $storeId,
                'entity_id'    => $product->getId(),
                'value'        => $urlKey,
            ];

            if ($this->versionHelper->isEe()) {
                unset($bind['entity_id']);

                $bind['row_id'] = $product->load($product->getId())->getData('row_id');
            }

            $connection->insert(
                $this->resource->getTableName('catalog_product_entity_varchar'),
                $bind
            );
        }
    }

    /**
     * @param int $productId
     * @param int $storeId
     *
     * @return \Magento\Catalog\Model\Product
     */
    private function getProduct($productId, $storeId)
    {
        return $this->productFactory->create()
                    ->setStoreId($storeId)
                    ->load($productId);
    }

    /**
     * @param \Magento\Catalog\Model\Product         $product
     * @param \Magento\Store\Api\Data\StoreInterface $store
     * @param string                                 $template
     *
     * @return string
     */
    private function getUrlKey($product, $store, $template)
    {
        $urlKey = $this->templateEngineService->render(
            $template,
            [
                'product' => $product,
                'store'   => $store,
            ]
        );

        $urlKey = $product->formatUrlKey($urlKey);

        return $urlKey;
    }
}
