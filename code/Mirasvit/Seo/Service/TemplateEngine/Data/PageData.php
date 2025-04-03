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

use Magento\Framework\View\Page\Config as PageConfig;

class PageData extends AbstractData
{
    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * PageData constructor.
     * @param PageConfig $pageConfig
     */
    public function __construct(
        PageConfig $pageConfig
    ) {
        $this->pageConfig = $pageConfig;

        parent::__construct();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle()
    {
        return __('Current Page Data');
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return [
            'title',
            'meta_description',
            'meta_keywords',
        ];
    }

    /**
     * @param string $attribute
     * @param array $additionalData
     * @return bool|false|mixed|string
     */
    public function getValue($attribute, $additionalData = [])
    {
        switch ($attribute) {
            case 'title':
                return $this->pageConfig->getTitle()->getShortHeading();

            case 'meta_description':
                return $this->pageConfig->getDescription();

            case 'meta_keywords':
                return $this->pageConfig->getKeywords();
        }

        return false;
    }
}
