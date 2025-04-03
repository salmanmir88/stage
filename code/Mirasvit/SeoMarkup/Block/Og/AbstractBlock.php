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



namespace Mirasvit\SeoMarkup\Block\Og;

use Magento\Framework\View\Element\Template;

abstract class AbstractBlock extends Template
{
    /**
     * @return array|false
     */
    abstract protected function getMeta();

    /**
     * @return string
     */
    protected function getMetaOptionKey()
    {
        return 'property';
    }

    /**
     * @return bool|string
     */
    protected function _toHtml()
    {
        $meta = $this->getMeta();

        if (!$meta) {
            return false;
        }

        $html = '' . PHP_EOL;

        foreach ($meta as $key => $value) {
            $value   = trim(strip_tags($value));
            $metaKey = $this->getMetaOptionKey();

            if ($value) {
                $html .= "<meta $metaKey=\"$key\" content=\"$value\"/>" . PHP_EOL;
            }
        }

        return $html;
    }
}
