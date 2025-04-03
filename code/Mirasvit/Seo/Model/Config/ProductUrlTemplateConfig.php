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



namespace Mirasvit\Seo\Model\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class ProductUrlTemplateConfig
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * ProductUrlTemplateConfig constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param null|string $store
     *
     * @return string
     */
    public function getProductUrlKey($store)
    {
        $productUrlKey = $this->scopeConfig->getValue(
            'seo/url/product_url_key',
            ScopeInterface::SCOPE_STORE,
            $store
        );

        return trim($productUrlKey);
    }
}
