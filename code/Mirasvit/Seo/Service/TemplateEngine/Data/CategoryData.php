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

use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class CategoryData extends AbstractData
{
    /**
     * @var \Magento\Catalog\Model\Category
     */
    private $category;

    private $registry;

    private $storeManager;

    public function __construct(
        Registry $registry,
        StoreManagerInterface $storeManager
    ) {
        $this->registry     = $registry;
        $this->storeManager = $storeManager;

        parent::__construct();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle()
    {
        return __('Category Data');
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
            'parent_name_[level]',
            'parent_url',
        ];
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
     * @return bool|false|mixed|string|null
     */
    public function getValue($attribute, $additionalData = [])
    {
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $this->getCategory();

        if (!$category) {
            return false;
        }

        switch ($attribute) {
            case 'page_title':
                return $category->getMetaTitle();

            case 'parent_name':
            case 'parent_name_1':
                $parent = $this->getParentCategory($category, 1);

                return $parent ? $parent->getName() : null;

            case 'parent_name_2':
                $parent = $this->getParentCategory($category, 2);

                return $parent ? $parent->getName() : null;

            case 'parent_name_3':
                $parent = $this->getParentCategory($category, 3);

                return $parent ? $parent->getName() : null;

            case 'parent_url':
                $parent = $this->getParentCategory($category);

                return $parent ? $parent->getUrl() : null;
        }

        return $category->getDataUsingMethod($attribute);
    }

    /**
     * @param \Magento\Catalog\Model\Category $category
     * @param int                             $level
     *
     * @return false|\Magento\Catalog\Model\Category
     */
    private function getParentCategory($category, $level = 1)
    {
        if (!$category) {
            return false;
        }

        /** @var \Magento\Store\Model\Store $store */
        $store = $this->storeManager->getStore();

        /** @var \Magento\Catalog\Model\Category $parent */
        $parent = $category->getParentCategory();

        if (!$parent) {
            return false;
        }

        if ($store->getRootCategoryId() == $parent->getId()) {
            return false;
        }

        if ($level <= 1) {
            return $parent;
        } else {
            return $this->getParentCategory($parent, $level - 1);
        }
    }
}
