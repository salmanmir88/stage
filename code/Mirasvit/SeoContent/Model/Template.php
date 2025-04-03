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



namespace Mirasvit\SeoContent\Model;

use Mirasvit\SeoContent\Api\Data\TemplateInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

class Template extends Content implements TemplateInterface
{
    /**
     * @var Template\Rule
     */
    private $rule;

    /**
     * @var Template\RuleFactory
     */
    private $ruleFactory;

    /**
     * Template constructor.
     * @param Template\RuleFactory $ruleFactory
     * @param Context $context
     * @param Registry $registry
     */
    public function __construct(
        Template\RuleFactory $ruleFactory,
        Context $context,
        Registry $registry
    ) {
        $this->ruleFactory = $ruleFactory;

        parent::__construct($context, $registry);
    }

    protected function _construct()
    {
        $this->_init(ResourceModel\Template::class);
    }

    /**
     * @param int $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setRuleType($value)
    {
        return $this->setData(self::RULE_TYPE, $value);
    }

    /**
     * @return int|Template
     */
    public function getRuleType()
    {
        return $this->getData(self::RULE_TYPE);
    }

    /**
     * @param string $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setName($value)
    {
        return $this->setData(self::NAME, $value);
    }

    /**
     * @return Template|string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param bool $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setIsActive($value)
    {
        return $this->setData(self::IS_ACTIVE, $value);
    }

    /**
     * @return bool|Template
     */
    public function isActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @param int $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setSortOrder($value)
    {
        return $this->setData(self::SORT_ORDER, $value);
    }

    /**
     * @return int|Template
     */
    public function getSortOrder()
    {
        return $this->getData(self::SORT_ORDER);
    }

    /**
     * @param bool $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setStopRuleProcessing($value)
    {
        return $this->setData(self::STOP_RULE_PROCESSING, $value);
    }

    /**
     * @return bool|Template
     */
    public function isStopRuleProcessing()
    {
        return $this->getData(self::STOP_RULE_PROCESSING);
    }

    /**
     * @param bool $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setApplyForChildCategories($value)
    {
        return $this->setData(self::APPLY_FOR_CHILD_CATEGORIES, $value);
    }

    /**
     * @return bool|Template
     */
    public function isApplyForChildCategories()
    {
        return $this->getData(self::APPLY_FOR_CHILD_CATEGORIES);
    }

    /**
     * @param string $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setConditionsSerialized($value)
    {
        return $this->setData(self::CONDITIONS_SERIALIZED, $value);
    }

    /**
     * @param array $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setStoreIds(array $value)
    {
        return $this->setData(self::STORE_IDS, implode(',', $value));
    }

    /**
     * @return array
     */
    public function getStoreIds()
    {
        return explode(',', $this->getData(self::STORE_IDS));
    }

    /**
     * @return Template\Rule
     */
    public function getRule()
    {
        if (!$this->rule) {
            $this->rule = $this->ruleFactory->create()
                ->setData(self::CONDITIONS_SERIALIZED, $this->getData(self::CONDITIONS_SERIALIZED))
                ->setData(self::ACTIONS_SERIALIZED, $this->getData(self::ACTIONS_SERIALIZED));
        }

        return $this->rule;
    }

    /**
     * @param bool $value
     * @return \Magento\Framework\Model\AbstractModel|\Mirasvit\SeoContent\Api\Data\ContentInterface|TemplateInterface
     */
    public function setApplyForHomepage($value)
    {
        return $this->setData(self::APPLY_FOR_HOMEPAGE, $value);
    }

    /**
     * @return bool|Template
     */
    public function isApplyForHomepage()
    {
        return $this->getData(self::APPLY_FOR_HOMEPAGE);
    }

    //    /**
    //     * @var int
    //     */
    //    protected $productId;
    //    /**
    //     * @var int
    //     */
    //    protected $categoryId;
    //
    //    const CACHE_TAG = 'seo_template';
    //
    //    /**
    //     * @var string
    //     */
    //    protected $_cacheTag = 'seo_template';//@codingStandardsIgnoreLine
    //    /**
    //     * @var string
    //     */
    //    protected $_eventPrefix = 'seo_template';//@codingStandardsIgnoreLine
    //
    //    /**
    //     * Get identities.
    //     *
    //     * @return array
    //     */
    //    public function getIdentities()
    //    {
    //        return [self::CACHE_TAG . '_' . $this->getId()];
    //    }
    //
    //    /**
    //     * @var \Mirasvit\SeoContent\Model\Template\Rule\Condition\CombineFactory
    //     */
    //    protected $templateRuleConditionCombineFactory;
    //
    //    /**
    //     * @var \Mirasvit\SeoContent\Model\Template\Action\CollectionFactory
    //     */
    //    protected $ruleActionCollectionFactory;
    //
    //    /**
    //     * @var \Magento\Catalog\Model\ProductFactory
    //     */
    //    protected $productFactory;
    //
    //    /**
    //     * @var \Magento\Catalog\Model\CategoryFactory
    //     */
    //    protected $categoryFactory;
    //
    //    /**
    //     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
    //     */
    //    protected $productCollectionFactory;
    //
    //    /**
    //     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
    //     */
    //    protected $categoryCollectionFactory;
    //
    //    /**
    //     * @var \Mirasvit\SeoContent\Model\ResourceModel\Template\CollectionFactory
    //     */
    //    protected $templateCollectionFactory;
    //
    //    /**
    //     * @var \Magento\Framework\Model\ResourceModel\Iterator
    //     */
    //    protected $resourceIterator;
    //
    //    /**
    //     * @var \Magento\Framework\Model\Context
    //     */
    //    protected $context;
    //
    //    /**
    //     * @var \Magento\Framework\Registry
    //     */
    //    protected $registry;
    //
    //    /**
    //     * @var \Magento\Framework\Model\ResourceModel\AbstractResource
    //     */
    //    protected $resource;
    //
    //    /**
    //     * @var \Magento\Framework\Data\Collection\AbstractDb
    //     */
    //    protected $resourceCollection;
    //
    //    /**
    //     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
    //     */
    //    public function __construct(
    //        \Mirasvit\SeoContent\Model\Template\Rule\Condition\CombineFactory $templateRuleConditionCombineFactory,
    //        \Mirasvit\SeoContent\Model\Template\Rule\Action\CollectionFactory $ruleActionCollectionFactory,
    //        \Magento\Catalog\Model\ProductFactory $productFactory,
    //        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
    //        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
    //        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
    //        \Mirasvit\SeoContent\Model\ResourceModel\Template\CollectionFactory $templateCollectionFactory,
    //        \Magento\Framework\Model\ResourceModel\Iterator $resourceIterator,
    //        \Magento\Framework\Model\Context $context,
    //        \Magento\Framework\Registry $registry,
    //        \Magento\Framework\Data\FormFactory $formFactory,
    //        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
    //        \Mirasvit\SeoContent\Model\Image\ImageFileABC $imageFile,
    //        \Magento\Catalog\Model\ImageUploader $imageUploader,
    //        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
    //        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
    //        array $data = []
    //    ) {
    //        $this->templateRuleConditionCombineFactory = $templateRuleConditionCombineFactory;
    //        $this->ruleActionCollectionFactory = $ruleActionCollectionFactory;
    //        $this->productFactory = $productFactory;
    //        $this->categoryFactory = $categoryFactory;
    //        $this->productCollectionFactory = $productCollectionFactory;
    //        $this->categoryCollectionFactory = $categoryCollectionFactory;
    //        $this->templateCollectionFactory = $templateCollectionFactory;
    //        $this->resourceIterator = $resourceIterator;
    //        $this->context = $context;
    //        $this->registry = $registry;
    //        $this->resource = $resource;
    //        $this->resourceCollection = $resourceCollection;
    //        $this->imageFile = $imageFile;
    //        $this->imageUploader = $imageUploader;
    //        parent::__construct($context, $registry, $formFactory, $localeDate, $resource, $resourceCollection, $data);
    //    }
    //
    //    /**
    //     * @return void
    //     */
    //    protected function _construct()
    //    {
    //        $this->_init('Mirasvit\SeoContent\Model\ResourceModel\Template');
    //    }
    //
    //    /**
    //     * @param bool|false $ruleId
    //     * @return \Mirasvit\SeoContent\Model\Template
    //     */
    //    public function getRule($ruleId = false)
    //    {
    //        $ruleId = ($ruleId) ? $ruleId : $this->getId();
    //
    //        $rule = $this->templateCollectionFactory->create()
    //            ->addFieldToFilter('template_id', $ruleId)
    //            ->getFirstItem();
    //        $rule = $rule->load($rule->getId());
    //
    //        return $rule;
    //    }
    //
    //    /**
    //     * @return \Mirasvit\SeoContent\Model\Template\Rule\Condition\Combine
    //     */
    //    public function getConditionsInstance()
    //    {
    //        return $this->templateRuleConditionCombineFactory->create();
    //    }
    //
    //    /**
    //     * @return Template\Rule\Action\Collection
    //     */
    //    public function getActionsInstance()
    //    {
    //        return $this->ruleActionCollectionFactory->create();
    //    }
    //
    //    /**
    //     * @param string|array $productId
    //     * @return bool
    //     */
    //    public function isProductApplied($productId)
    //    {
    //        $isArray = is_array($productId) ? true : false;
    //        if ($this->productId === null) {
    //            $this->setCollectedAttributes([]);
    //            $condition = ($isArray) ? ['in' => $productId] : $productId;
    //            $productCollection = $this->productCollectionFactory->create()->addFieldToFilter('entity_id', $condition);
    //            $this->getConditions()->collectValidatedAttributes($productCollection);
    //
    //            $this->resourceIterator->walk(
    //                $productCollection->getSelect(),
    //                [[$this, 'callbackValidateProduct']],
    //                [
    //                    'attributes' => $this->getCollectedAttributes(),
    //                    'product'    => $this->productFactory->create(),
    //                ]
    //            );
    //        }
    //
    //        if ($this->productId && $isArray) {
    //            return $this->productId;
    //        } elseif ($this->productId) {
    //            return true;
    //        }
    //
    //        return false;
    //    }
    //
    //    /**
    //     * @param string $args
    //     *
    //     * @return void
    //     */
    //    public function callbackValidateProduct($args)
    //    {
    //        $product = clone $args['product'];
    //        $product->setData($args['row']);
    //        if ($this->getConditions()->validate($product)) {
    //            $this->productId[] = $product->getId();
    //        }
    //    }
    //
    //    /**
    //     * @param string $categoryId
    //     * @return bool
    //     */
    //    public function isCategoryApplied($categoryId)
    //    {
    //        if ($this->categoryId === null) {
    //            $this->categoryId = [];
    //            $this->setCollectedAttributes([]);
    //            $categoryCollection = $this->categoryCollectionFactory->create()->addFieldToFilter(
    //                'entity_id',
    //                $categoryId
    //            );
    //            $this->getConditions()->collectValidatedAttributes($categoryCollection);
    //
    //            $this->resourceIterator->walk(
    //                $categoryCollection->getSelect(),
    //                [[$this, 'callbackValidateCategory']],
    //                [
    //                    'attributes' => $this->getCollectedAttributes(),
    //                    'category'   => $this->categoryFactory->create(),
    //                ]
    //            );
    //        }
    //
    //        if ($this->categoryId) {
    //            return true;
    //        }
    //
    //        return false;
    //    }
    //
    //    /**
    //     * @param string $args
    //     *
    //     * @return void
    //     */
    //    public function callbackValidateCategory($args)
    //    {
    //        $category = clone $args['category'];
    //        $category->setData($args['row']);
    //        if ($this->getConditions()->validate($category)) {
    //            $this->categoryId[] = $category->getId();
    //        }
    //    }
    //
    //    /**
    //     * Retrieve rule combine conditions model
    //     *
    //     * @return \Magento\Rule\Model\Condition\Combine
    //     */
    //    public function getConditions()
    //    {
    //        if (empty($this->_conditions)) {
    //            $this->_resetConditions();
    //        }
    //
    //        // Load rule conditions if it is applicable
    //        if ($this->hasConditionsSerialized()) {
    //            $conditions = $this->getConditionsSerialized();
    //            if (!empty($conditions)) {
    //                $decode = json_decode($conditions);
    //                if ($decode) { //M2.2 compatibility
    //                    $conditions = $this->serializer->unserialize($conditions);
    //                } else {
    //                    $conditions = unserialize($conditions);
    //                }
    //                if (is_array($conditions) && !empty($conditions)) {
    //                    $this->_conditions->loadArray($conditions);
    //                }
    //            }
    //            $this->unsConditionsSerialized();
    //        }
    //
    //        return $this->_conditions;
    //    }
    //
    //    /**
    //     * {@inheritdoc}
    //     */
    //    public function afterSave()
    //    {
    //        $categoryImage = $this->getData('category_image');
    //        $this->moveFileFromTmp($categoryImage);
    //
    //        return parent::afterSave();
    //    }
    //
    //    /**
    //     * @param string $image
    //     * @return void
    //     */
    //    private function moveFileFromTmp($categoryImage)
    //    {
    //        if ($categoryImage
    //            && !$this->imageFile->isExist($categoryImage)) {
    //            $this->imageUploader->moveFileFromTmp($categoryImage);
    //        }
    //    }
}
