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



namespace Mirasvit\Seo\Service\TemplateEngine\Data;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Framework\Pricing\Helper\Data as PricingHelper;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class ProductData extends AbstractData
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;

    private $categoryFactory;

    private $catalogHelper;

    private $pricingHelper;

    private $registry;

    private $storeManager;

    public function __construct(
        Registry $registry,
        CatalogHelper $catalogHelper,
        CategoryFactory $categoryFactory,
        StoreManagerInterface $storeManager,
        PricingHelper $pricingHelper
    ) {
        $this->registry        = $registry;
        $this->catalogHelper   = $catalogHelper;
        $this->categoryFactory = $categoryFactory;
        $this->storeManager    = $storeManager;
        $this->pricingHelper   = $pricingHelper;

        parent::__construct();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle()
    {
        return __('Product Data');
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return [
            'name',
            'url',
            'page_title',
            'parent_name',
            'parent_url',
            'parent_parent_name',
            'category_name',
        ];
    }

    /**
     * Used in GraphQl
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return $this
     */
    public function setProduct($product)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getProduct()
    {
        if (!$this->product) {
            return $this->registry->registry('current_product');
        }

        return $this->product;
    }

    /**
     * Used in GraphQl
     *
     * @param \Magento\Catalog\Model\Category $category
     *
     * @return $this
     */
    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * @return \Magento\Catalog\Model\Category
     */
    public function getCategory()
    {
        if (!$this->category) {
            return $this->registry->registry('current_category');
        }

        return $this->category;
    }

    /**
     * @param string $attribute
     * @param array $additionalData
     * @return bool|false|float|mixed|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getValue($attribute, $additionalData = [])
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = isset($additionalData['product'])
            ? $additionalData['product']
            : $this->getProduct();

        $storeId = isset($additionalData['store'])
            ? $additionalData['store']->getId()
            : $this->storeManager->getStore()->getId();

        if (!$product) {
            return false;
        }

        switch ($attribute) {
            case 'price':
                $price = null;
                if ($product->getTypeId() === 'simple') {
                    //other products types include tax by default
                    $price = $this->catalogHelper->getTaxPrice($product, $product->getFinalPrice());
                } else {
                    $price = $product->getFinalPrice();
                }

                return $this->pricingHelper->currency($price, false, false);

            case 'url':
                return $product->getProductUrl();

            case 'name':
                if (!$product->getName()) {
                    $product = $product->load($product->getId());
                }

                return $product->getName();

            case 'sku':
                return $product->getSku();

            case 'category_name':
                if ($category = $this->getCategory()) {
                    return $category->getName();
                }

                $categoryIds = $product->getCategoryIds();
                $categoryIds = array_reverse($categoryIds);

                if (isset($categoryIds[0])) {
                    return $this->categoryFactory->create()
                        ->setStoreId($storeId)
                        ->load($categoryIds[0])
                        ->getName();
                }

                return false;
        }

        if ($attributes = $product->getAttributes()) {
            foreach ($attributes as $attr) {
                if (isset($additionalData['store'])) {
                    // required for use correct attribute labels (color Black FR) during url-generation
                    $attr->setStoreId($storeId);
                }

                if ($attr->getAttributeCode() === $attribute) {
                    $value = $attr->getFrontend()->getValue($product);

                    if (empty($value)) {
                        $value = $product->getResource()->getAttributeRawValue($product->getId(), $attribute, $storeId);
                    }

                    if (is_array($value)) {
                        if (!empty($value)) {
                            $value = array_values($value)[0];
                        } else {
                            $value = null;
                        }
                    }

                    return $value;
                }
            }
        }

        return $product->getDataUsingMethod($attribute);
    }
}
