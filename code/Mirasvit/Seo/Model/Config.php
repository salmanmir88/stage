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



namespace Mirasvit\Seo\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Core\Service\SerializeService;
use Mirasvit\Seo\Helper\Serializer;
use Mirasvit\Seo\Model\Cookie\Cookie;

/**
 * @SuppressWarnings(PHPMD.ExcessivePublicCount)
 */
class Config
{
    const TRAILING_SLASH_DISABLE = 0;
    const NO_TRAILING_SLASH      = 1;
    const TRAILING_SLASH         = 2;

    const URL_FORMAT_SHORT = 1;
    const URL_FORMAT_LONG  = 2;

    const NOINDEX_NOFOLLOW = 1;
    const NOINDEX_FOLLOW   = 2;
    const INDEX_NOFOLLOW   = 3;
    const INDEX_FOLLOW     = 4;

    const PRODUCTS_WITH_REVIEWS_NUMBER = 1;
    const REVIEWS_NUMBER               = 2;
    const OPENGRAPH_LOGO_IMAGE         = 1;
    const OPENGRAPH_PRODUCT_IMAGE      = 2;
    const INFO_IP                      = 1;
    const INFO_COOKIE                  = 2;
    const COOKIE_DEL_BUTTON            = 'Delete cookie';


    //seo template rule

    // open graph

    const COOKIE_ADD_BUTTON = 'Add cookie';
    const BYPASS_COOKIE     = 'info_bypass_cookie';

    //seo info

    private $scopeConfig;

    private $cookie;

    private $storeManager;

    private $serializer;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Cookie $cookie,
        StoreManagerInterface $storeManager,
        Serializer $serializer
    ) {
        $this->scopeConfig  = $scopeConfig;
        $this->cookie       = $cookie;
        $this->storeManager = $storeManager;
        $this->serializer   = $serializer;
    }

    /**
     * @return bool
     */
    public function isAddCanonicalUrl()
    {
        return $this->scopeConfig->getValue('seo/general/is_add_canonical_url');
    }

    /**
     * @return bool
     */
    public function isAddLongestCanonicalProductUrl()
    {
        return $this->scopeConfig->getValue('seo/general/is_longest_canonical_url');
    }


    /**
     * @return int
     */
    public function getAssociatedCanonicalConfigurableProduct()
    {
        return $this->scopeConfig->getValue('seo/general/associated_canonical_configurable_product');
    }

    /**
     * @return int
     */
    public function getAssociatedCanonicalGroupedProduct()
    {
        return $this->scopeConfig->getValue('seo/general/associated_canonical_grouped_product');
    }

    /**
     * @return int
     */
    public function getAssociatedCanonicalBundleProduct()
    {
        return $this->scopeConfig->getValue('seo/general/associated_canonical_bundle_product');
    }

    /**
     * @param int|bool $store
     *
     * @return int
     */
    public function getCanonicalStoreWithoutStoreCode($store = null)
    {
        return $this->scopeConfig->getValue(
            'seo/general/canonical_store_without_store_code',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|bool $store
     *
     * @return int
     */
    public function getCrossDomainStore($store = null)
    {
        return $this->scopeConfig->getValue(
            'seo/general/crossdomain',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|bool $store
     *
     * @return int
     */
    public function isPreferCrossDomainHttps($store = null)
    {
        return $this->scopeConfig->getValue(
            'seo/general/crossdomain_prefer_https',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return bool
     */
    public function isPaginatedCanonical()
    {
        return $this->scopeConfig->getValue('seo/general/paginated_canonical');
    }

    /**
     * @return array
     */
    public function getCanonicalUrlIgnorePages()
    {
        $pages = $this->scopeConfig->getValue('seo/general/canonical_url_ignore_pages');
        $pages = explode("\n", trim($pages));
        $pages = array_map('trim', $pages);

        return $pages;
    }

    /**
     * @param int|null $store
     *
     * @return array
     */
    public function getNoindexPages($store = null)
    {
        if (empty($store)) {
            $store = $this->storeManager->getStore()->getId();
        }

        $storePages = $this->getOptionData(
            $this->scopeConfig->getValue(
                'seo/general/noindex_pages2',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            )
        );

        $generalPages = $this->getOptionData(
            $this->scopeConfig->getValue('seo/general/noindex_pages2')
        );

        $pages = array_merge($storePages, $generalPages);

        $result = [];

        foreach ($pages as $value) {
            if (!is_array($value)) {
                continue;
            }

            $result[] = new \Magento\Framework\DataObject($value);
        }

        return $result;
    }

    /**
     * @return int
     */
    public function getHttpsNoindexPages()
    {
        return $this->scopeConfig->getValue('seo/general/https_noindex_pages');
    }

    /**
     * @return bool
     */
    public function isPagingPrevNextEnabled()
    {
        return $this->scopeConfig->getValue('seo/general/is_paging_prevnext');
    }

    /**
     * @return bool
     */
    public function isCategoryMetaTagsUsed()
    {
        return $this->scopeConfig->getValue('seo/general/is_category_meta_tags_used');
    }

    /**
     * @return bool
     */
    public function isProductMetaTagsUsed()
    {
        return $this->scopeConfig->getValue('seo/general/is_product_meta_tags_used');
    }

    /**
     * @return bool
     */
    public function isUseHtmlSymbolsInMetaTags()
    {
        return $this->scopeConfig->getValue('seo/general/is_use_html_symbols_in_meta_tags');
    }

    /**
     * @return bool
     */
    public function isUseShortDescriptionForCategories()
    {
        return $this->scopeConfig->getValue('seo/general/is_use_short_description_for_categories');
    }

    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return int
     */
    public function getMetaDescriptionPageNumber($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_description_page_number',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return int
     */
    public function getMetaTitleMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_title_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return int
     */
    public function getMetaDescriptionMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/meta_description_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return int
     */
    public function getProductNameMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/product_name_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return int
     */
    public function getProductShortDescriptionMaxLength($store)
    {
        return $this->scopeConfig->getValue(
            'seo/extended/product_short_description_max_length',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     *
     * @return bool
     */
    public function isRedirectToLowercaseEnabled($store)
    {
        return (bool)$this->scopeConfig->getValue(
            'seo/extended/redirect_to_lowercase',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int $store
     * @return array
     */
    public function getAllowedLowercasePageTypes($store)
    {
        $data = SerializeService::decode($this->scopeConfig->getValue(
            'seo/extended/to_lowercase_allowed_types',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        ));

        $result = [];
        foreach ($data as $item) {
            if (isset($item['expression'])) {
                $result[] = $item['expression'];
            }
        }

        return $result;
    }

    /**
     * SEO URL
     * @return bool
     */
    public function isEnabledSeoUrls()
    {
        return $this->scopeConfig->getValue('seo/url/layered_navigation_friendly_urls');
    }

    /**
     * @return int
     */
    public function getTrailingSlash()
    {
        return $this->scopeConfig->getValue('seo/url/trailing_slash');
    }

    /**
     * @return int
     */
    public function getProductUrlFormat()
    {
        return $this->scopeConfig->getValue('seo/url/product_url_format');
    }

    /**
     * @param \Magento\Store\Model\Store $store
     *
     * @return string
     */
    public function getProductUrlKey($store)
    {
        return $this->scopeConfig->getValue(
            'seo/url/product_url_key',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @param int|null $storeId
     * @param int|null $websiteId
     *
     * @return bool
     */
    public function isEnabledRemoveParentCategoryPath($storeId = null, $websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                'seo/url/use_category_short_url',
                \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            'seo/url/use_category_short_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * INFO
     *
     * @param null $storeId
     *
     * @return bool
     */
    public function isInfoEnabled($storeId = null)
    {
        if (!$this->_isInfoAllowed()) {
            return false;
        }

        return $this->scopeConfig->getValue(
            'seo/info/info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isShowAltLinkInfo($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'seo/info/alt_link_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    public function isShowTemplatesRewriteInfo($storeId = null)
    {
        return $this->scopeConfig->getValue(
            'seo/info/templates_rewrite_info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Check if "Use Categories Path for Product URLs" enabled
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function isProductLongUrlEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            \Magento\Catalog\Helper\Product::XML_PATH_PRODUCT_URL_USE_CATEGORY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @return string
     */
    public function isAddStoreCodeToUrlsEnabled()
    {
        return $this->scopeConfig->getValue(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL);
    }

    /**
     * @param int $storeId
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isUrlKeyRewriteEnabled($storeId)
    {
        return $this->scopeConfig->getValue(
            'seo/url/apply_url_key_for_new_products',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     *
     * @return bool
     */
    protected function _isInfoAllowed($storeId = null)
    {
        $info = $this->scopeConfig->getValue(
            'seo/info/info',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (($info == self::INFO_COOKIE)
            && $this->cookie->isCookieExist()) {
            return true;
        } elseif ($info == self::INFO_IP) {
            $ips = $this->scopeConfig->getValue(
                'seo/info/allowed_ip',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            if ($ips == '') {
                return true;
            }
            if (!isset($_SERVER['REMOTE_ADDR'])) {
                return false;
            }
            $ips = explode(',', $ips);
            $ips = array_map('trim', $ips);

            return in_array($_SERVER['REMOTE_ADDR'], $ips);
        }

        return false;
    }

    /**
     * @param string $data
     *
     * @return array
     */
    private function getOptionData($data)
    {
        $result = [];

        if ($decode = json_decode($data, true)) {
            $result = $decode;
        } else {
            $result = $this->serializer->unserialize($data);
        }

        return $result;
    }
}
