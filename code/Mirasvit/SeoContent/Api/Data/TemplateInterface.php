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



namespace Mirasvit\SeoContent\Api\Data;

interface TemplateInterface extends ContentInterface
{
    const RULE_TYPE_PRODUCT    = 1;
    const RULE_TYPE_CATEGORY   = 2;
    const RULE_TYPE_NAVIGATION = 3;
    const RULE_TYPE_PAGE       = 4;

    const TABLE_NAME = 'mst_seo_content_template';

    const ID = 'template_id';

    const RULE_TYPE = 'rule_type';

    const NAME       = 'name';
    const IS_ACTIVE  = 'is_active';
    const SORT_ORDER = 'sort_order';

    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED    = 'actions_serialized';
    const STOP_RULE_PROCESSING  = 'stop_rules_processing';

    const APPLY_FOR_CHILD_CATEGORIES = 'apply_for_child_categories';
    const APPLY_FOR_HOMEPAGE = 'apply_for_homepage';

    const STORE_IDS = 'store_ids';

    /**
     * @return int
     */
    public function getId();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setRuleType($value);

    /**
     * @return int
     */
    public function getRuleType();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setName($value);

    /**
     * @return string
     */
    public function getName();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setIsActive($value);

    /**
     * @return bool
     */
    public function isActive();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setSortOrder($value);

    /**
     * @return int
     */
    public function getSortOrder();


    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setStopRuleProcessing($value);

    /**
     * @return bool
     */
    public function isStopRuleProcessing();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setApplyForChildCategories($value);

    /**
     * @return bool
     */
    public function isApplyForChildCategories();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setConditionsSerialized($value);

    /**
     * @param array $value
     *
     * @return $this
     */
    public function setStoreIds(array $value);

    /**
     * @return array
     */
    public function getStoreIds();

    /**
     * @return \Mirasvit\SeoContent\Model\Template\Rule
     */
    public function getRule();

    /**
     * @param bool $value
     *
     * @return $this
     */
    public function setApplyForHomepage($value);

    /**
     * @return bool
     */
    public function isApplyForHomepage();
}
