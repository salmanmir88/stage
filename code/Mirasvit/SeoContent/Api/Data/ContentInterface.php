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

interface ContentInterface
{
    const DESCRIPTION_POSITION_DISABLED                = 0;
    const DESCRIPTION_POSITION_BOTTOM_PAGE             = 1;
    const DESCRIPTION_POSITION_UNDER_SHORT_DESCRIPTION = 2;
    const DESCRIPTION_POSITION_UNDER_FULL_DESCRIPTION  = 3;
    const DESCRIPTION_POSITION_UNDER_PRODUCT_LIST      = 4;
    const DESCRIPTION_POSITION_CUSTOM_TEMPLATE         = 5;


    const TITLE            = 'title';
    const META_TITLE       = 'meta_title';
    const META_KEYWORDS    = 'meta_keywords';
    const META_DESCRIPTION = 'meta_description';
    const META_ROBOTS      = 'meta_robots';

    const DESCRIPTION          = 'description';
    const DESCRIPTION_POSITION = 'description_position';
    const DESCRIPTION_TEMPLATE = 'description_template';
    const SHORT_DESCRIPTION    = 'short_description';
    const FULL_DESCRIPTION     = 'full_description';


    const CATEGORY_DESCRIPTION = 'category_description';
    const CATEGORY_IMAGE       = 'category_image';

    const APPLIED_TEMPLATE_ID = 'applied_template_id';
    const APPLIED_REWRITE_ID  = 'applied_rewrite_id';

    /**
     * @param string $key
     *
     * @return $this
     */
    public function getData($key);

    /**
     * @param string $key
     * @param string $value
     *
     * @return $this
     */
    public function setData($key, $value);

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTitle($value);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaTitle($value);

    /**
     * @return string
     */
    public function getMetaTitle();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaKeywords($value);

    /**
     * @return string
     */
    public function getMetaKeywords();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaDescription($value);

    /**
     * @return string
     */
    public function getMetaDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDescription($value);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setDescriptionPosition($value);

    /**
     * @return int
     */
    public function getDescriptionPosition();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setDescriptionTemplate($value);

    /**
     * @return string
     */
    public function getDescriptionTemplate();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setShortDescription($value);

    /**
     * @return string
     */
    public function getShortDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setFullDescription($value);

    /**
     * @return string
     */
    public function getFullDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryDescription($value);

    /**
     * @return string
     */
    public function getCategoryDescription();

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setCategoryImage($value);

    /**
     * @return string
     */
    public function getCategoryImage();


    /**
     * @param string $value
     *
     * @return $this
     */
    public function setMetaRobots($value);

    /**
     * @return string
     */
    public function getMetaRobots();
}
