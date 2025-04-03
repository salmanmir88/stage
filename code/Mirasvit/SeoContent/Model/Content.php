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

use Magento\Framework\Model\AbstractModel;
use Mirasvit\SeoContent\Api\Data\ContentInterface;

class Content extends AbstractModel implements ContentInterface
{
    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setTitle($value)
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * @return Content|string
     */
    public function getTitle()
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setMetaTitle($value)
    {
        return $this->setData(self::META_TITLE, $value);
    }

    /**
     * @return Content|string
     */
    public function getMetaTitle()
    {
        return $this->getData(self::META_TITLE);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setMetaKeywords($value)
    {
        return $this->setData(self::META_KEYWORDS, $value);
    }

    /**
     * @return Content|string
     */
    public function getMetaKeywords()
    {
        return $this->getData(self::META_KEYWORDS);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setMetaDescription($value)
    {
        return $this->setData(self::META_DESCRIPTION, $value);
    }

    /**
     * @return Content|string
     */
    public function getMetaDescription()
    {
        return $this->getData(self::META_DESCRIPTION);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setDescription($value)
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    /**
     * @return Content|string
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @param int $value
     * @return AbstractModel|ContentInterface
     */
    public function setDescriptionPosition($value)
    {
        return $this->setData(self::DESCRIPTION_POSITION, $value);
    }

    /**
     * @return int|Content
     */
    public function getDescriptionPosition()
    {
        return $this->getData(self::DESCRIPTION_POSITION);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setDescriptionTemplate($value)
    {
        return $this->setData(self::DESCRIPTION_TEMPLATE, $value);
    }

    /**
     * @return Content|string
     */
    public function getDescriptionTemplate()
    {
        return $this->getData(self::DESCRIPTION_TEMPLATE);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setShortDescription($value)
    {
        return $this->setData(self::SHORT_DESCRIPTION, $value);
    }

    /**
     * @return Content|string
     */
    public function getShortDescription()
    {
        return $this->getData(self::SHORT_DESCRIPTION);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setFullDescription($value)
    {
        return $this->setData(self::FULL_DESCRIPTION, $value);
    }

    /**
     * @return Content|string
     */
    public function getFullDescription()
    {
        return $this->getData(self::FULL_DESCRIPTION);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setCategoryDescription($value)
    {
        return $this->setData(self::CATEGORY_DESCRIPTION, $value);
    }

    /**
     * @return Content|string
     */
    public function getCategoryDescription()
    {
        return $this->getData(self::CATEGORY_DESCRIPTION);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setCategoryImage($value)
    {
        return $this->setData(self::CATEGORY_IMAGE, $value);
    }

    /**
     * @return string
     */
    public function getCategoryImage()
    {
        return $this->getData(self::CATEGORY_IMAGE);
    }

    /**
     * @param string $value
     * @return AbstractModel|ContentInterface
     */
    public function setMetaRobots($value)
    {
        return $this->setData(self::META_ROBOTS, $value);
    }

    /**
     * @return string
     */
    public function getMetaRobots()
    {
        return $this->getData(self::META_ROBOTS);
    }
}
