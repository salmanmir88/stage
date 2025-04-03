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



namespace Mirasvit\Seo\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CheckUrlObserver implements ObserverInterface
{
    const HOME_PAGE_REDIRECT = 'm__home_page_index_redirect';
    const REDIRECT_CHAIN     = '[redirect_chain]';

    /**
     * @var \Mirasvit\Seo\Model\RedirectFactory
     */
    protected $redirectFactory;

    /**
     * @var \Mirasvit\Seo\Helper\Redirect
     */
    protected $redirectHelper;

    /**
     * @var \Mirasvit\Seo\Helper\Data
     */
    protected $dataHelper;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    protected $response;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlManager;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * true - decode URLs when redirect from Redirect Manager
     * false - work with URLs "as is"
     */
    protected $_redirectUrlFromDecode = true;

    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    private $config;

    /**
     * CheckUrlObserver constructor.
     *
     * @param \Mirasvit\Seo\Model\RedirectFactory                $redirectFactory
     * @param \Mirasvit\Seo\Helper\Redirect                      $redirectHelper
     * @param \Mirasvit\Seo\Helper\Data                          $dataHelper
     * @param \Magento\Framework\App\Request\Http                $request
     * @param \Magento\Framework\App\Response\Http               $response
     * @param \Magento\Framework\UrlInterface                    $urlManager
     * @param \Magento\Store\Model\StoreManagerInterface         $storeManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Mirasvit\Seo\Model\Config                         $config
     * @param \Magento\Customer\Model\Session                    $customerSession
     */
    public function __construct(
        \Mirasvit\Seo\Model\RedirectFactory $redirectFactory,
        \Mirasvit\Seo\Helper\Redirect $redirectHelper,
        \Mirasvit\Seo\Helper\Data $dataHelper,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\UrlInterface $urlManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->redirectFactory = $redirectFactory;
        $this->redirectHelper  = $redirectHelper;
        $this->dataHelper      = $dataHelper;
        $this->request         = $request;
        $this->response        = $response;
        $this->urlManager      = $urlManager;
        $this->storeManager    = $storeManager;
        $this->scopeConfig     = $scopeConfig;
        $this->config          = $config;
        $this->customerSession = $customerSession;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $url = $this->request->getRequestUri();

        if (!isset($_SERVER['REQUEST_URI'])) {
            return;
        }

        $fullUrl = $_SERVER['REQUEST_URI'];

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            return;
        }

        if ($this->request->isAjax()) {
            return;
        }

        if (strpos($this->request->getRequestUri(), 'page_cache') !== false) {
            //prevent for varnish urls such /page_cache/block/esi/blocks/%5B%22catalog.topnav%22%5D/handles/WyJkZW
            return;
        }
        if (strpos($this->request->getRequestUri(), 'paypal') !== false) {
            return;
        }
        if (strpos($this->request->getRequestUri(), 'checkout') !== false) {
            return;
        }
        // prevent Invalid header value detected with Magesolution Pagebuilder
        if (strpos($this->request->getRequestUri(), 'fbuilder') !== false) {
            return;
        }
        // redirect uppercase to lowercase
        $p = strpos($fullUrl, "?");
        if ($p === false) {
            $newUrl = strtolower($fullUrl);
        } else {
            //dont lowercase get params
            $mainUrl = substr($fullUrl, 0, $p);
            $query   = substr($fullUrl, $p);
            $newUrl  = strtolower($mainUrl) . $query;
        }

        if ($this->config->isRedirectToLowercaseEnabled($this->storeManager->getStore()->getStoreId())
            && $fullUrl != $newUrl) {

            $allowedTypes = $this->config->getAllowedLowercasePageTypes($this->storeManager->getStore()->getStoreId());

            $allowed = count($allowedTypes) === 0;
            foreach ($allowedTypes as $type) {
                if (preg_match($type, $this->request->getFullActionName())) {
                    $allowed = true;
                }
            }

            if ($allowed) {
                $this->redirectHelper->redirect($this->response, $newUrl);

                return;
            }
        }

        $this->redirectFromRedirectManagerUrlList($this->response);
        $this->redirectHelper->unsetFlag();

        $urlToRedirect = $this->redirectHelper->getUrlWithCorrectEndSlash($url);

        if ($urlToRedirect != '/' && $url != $urlToRedirect) {
            $this->redirectHelper->redirect($this->response, rtrim($this->urlManager->getBaseUrl(), '/') . $urlToRedirect);
        }

        if (substr($fullUrl, -4, 4) == '?p=1') {
            $this->redirectHelper->redirect($this->response, substr($fullUrl, 0, -4));
        }

        //prevent redirect loop if $fullUrl always contains index.php
        if (in_array(trim($fullUrl, '/'), ['index.php'])
            && !$this->customerSession->getData(self::HOME_PAGE_REDIRECT)) {
            $this->customerSession->setData(self::HOME_PAGE_REDIRECT, 1);
            $this->redirectHelper->redirect($this->response, '/');
        } elseif (!in_array(trim($fullUrl, '/'), ['index.php'])) {
            $this->customerSession->unsetData(self::HOME_PAGE_REDIRECT);
        }

        if (in_array(trim($fullUrl, '/'), ['home', 'index.php/home'])) {
            $this->redirectHelper->redirect($this->response, '/');
        }
    }

    /**
     * Get base url (some stores can drop with error if we use default magento getBaseUrl here)
     * @return string
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getSeoBaseUrl()
    {
        $httpHostWithPort = $this->request->getHttpHost(false);
        $httpHostWithPort = explode(':', $httpHostWithPort);
        $httpHost         = isset($httpHostWithPort[0]) ? $httpHostWithPort[0] : '';
        $port             = '';
        if (isset($httpHostWithPort[1])) {
            $defaultPorts = [
                \Magento\Framework\App\Request\Http::DEFAULT_HTTP_PORT,
                \Magento\Framework\App\Request\Http::DEFAULT_HTTPS_PORT,
            ];
            if (!in_array($httpHostWithPort[1], $defaultPorts)) {
                /** Custom port */
                $port = ':' . $httpHostWithPort[1];
            }
        }

        $storeCodeInUrl = '';
        if ($this->storeManager->getStore()->getConfig(\Magento\Store\Model\Store::XML_PATH_STORE_IN_URL)) {
            $storeCodeInUrl = '/' . $this->storeManager->getStore()->getCode() . '/';
        }

        $baseUrl = $this->request->getScheme() . '://' . $httpHost . $port;

        if ($storeCodeInUrl && strpos($this->urlManager->getCurrentUrl(), $baseUrl . $storeCodeInUrl) !== false) {
            $baseUrl = $baseUrl . $storeCodeInUrl;
        }

        if (strpos($this->request->getScheme(), 'https') !== false) {
            $configPath = 'web/secure/base_url';
        } else {
            $configPath = 'web/unsecure/base_url';
        }
        $baseConfigUrl = $this->scopeConfig->getValue(
            $configPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->storeManager->getStore()->getId()
        );

        if (substr_count($baseConfigUrl, "/") > 3) {
            $baseUrl = $baseConfigUrl;
        }

        if (substr($baseUrl, -1) != "/") {
            $baseUrl .= "/";
        }

        return $baseUrl;
    }

    /**
     * @param string $urlFrom
     *
     * @return string
     */
    protected function prepareRedirectUrl($urlFrom)
    {
        if (stripos($urlFrom, 'http://') === false
            && stripos($urlFrom, 'https://') === false
        ) {
            return $this->urlManager->getBaseUrl() . ltrim($urlFrom, '/');
        }

        return $urlFrom;
    }

    /**
     * Do redirect using records of our Redirect Manager.
     *
     * @param \Magento\Framework\App\ResponseInterface $response
     *
     * @return bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function redirectFromRedirectManagerUrlList($response)
    {
        \Magento\Framework\Profiler::start(__METHOD__);

        $currentUrl = $this->urlManager->getCurrentUrl();
        if ($this->_redirectUrlFromDecode) {
            $currentUrl = rawurldecode($currentUrl);
        }
        $currentAction = $this->dataHelper->getFullActionCode();
        $baseUrl       = $this->getSeoBaseUrl();

        /** @var \Mirasvit\Seo\Model\ResourceModel\Redirect\Collection $redirectCollection */
        $redirectCollection = $this->redirectFactory->create()
            ->getCollection();
        $redirectCollection
            ->addActiveFilter()
            ->addStoreFilter($this->storeManager->getStore());

        $url        = str_replace($baseUrl, '', $currentUrl);
        $trimmedUrl = str_replace(rtrim($baseUrl, '/'), '', $currentUrl);
        $where      = 'url_from = ' . "'" . addslashes($currentUrl) . "'"
            . ' OR ' . 'url_from = ' . "'" . addslashes($url) . "'"
            . ' OR ' . 'url_from = ' . "'" . addslashes($trimmedUrl) . "'"
            . ' OR ' . "'" . addslashes($url) . "'" . " LIKE CONCAT(REPLACE(url_from, '*', '%'))"
            . ' OR ' . "'" . addslashes($trimmedUrl) . "'" . " LIKE CONCAT(REPLACE(url_from, '*', '%'))"
            . ' OR ' . "'" . addslashes($currentUrl) . "'" . " LIKE CONCAT(REPLACE(url_from, '*', '%'))"
            . ' OR ' . "'" . addslashes($url) . "'"
            . " LIKE CONCAT(REPLACE(url_from, '" . self::REDIRECT_CHAIN . "', '%'))"
            . ' OR ' . "'" . addslashes($trimmedUrl) . "'"
            . " LIKE CONCAT(REPLACE(url_from, '" . self::REDIRECT_CHAIN . "', '%'))"
            . ' OR ' . "'" . addslashes($currentUrl) . "'"
            . " LIKE CONCAT(REPLACE(url_from, '" . self::REDIRECT_CHAIN . "', '%'))";

        $redirectCollection->getSelect()
            ->where(new \Zend_Db_Expr($where), null, \Magento\Framework\DB\Select::TYPE_CONDITION)
            ->order('LENGTH(url_from) DESC');

        foreach ($redirectCollection as $redirect) {
            $urlFrom = $this->prepareRedirectUrl($redirect->getUrlFrom());
            $urlTo   = $this->prepareRedirectUrl($redirect->getUrlTo());
            $action  = $redirect->getIsRedirectOnlyErrorPage();

            if ($action && $currentAction != 'cms_noroute_index') {
                continue;
            }

            if (strpos($urlTo, '[redirect_chain]') !== false) {
                $urlTo   = $this->getRedirectChainUrlTo($urlFrom, $urlTo, $currentUrl);
                $urlFrom = $currentUrl;
            }

            if (!$urlTo) {
                continue;
            }

            // To prevent redirect loop is rule is set up incorrectly
            if ($this->redirectHelper->checkRedirectPattern($redirect->getUrlFrom(), $redirect->getUrlTo(), $action) && $urlFrom == $urlTo) {
                continue;
            }

            if ($this->redirectHelper->checkForLoop($urlTo)) {
                continue;
            }

            if ($currentUrl == $urlFrom
                || (stripos($redirect->getUrlFrom(), '*') !== false
                    && $this->redirectHelper->checkRedirectPattern($redirect->getUrlFrom(), $currentUrl))) {
                $this->redirectHelper->setFlag($currentUrl);
                $this->redirectHelper->redirect($response, $urlTo, $redirect->getRedirectType());
                break;
            }
        }

        \Magento\Framework\Profiler::stop(__METHOD__);

        return false;
    }

    /**
     * @param string $urlFrom
     * @param string $urlTo
     * @param string $currentUrl
     *
     * @return bool|string
     */
    private function getRedirectChainUrlTo($urlFrom, $urlTo, $currentUrl)
    {
        $urlToPostfix = $this->getUrlToPostfix($urlFrom, $urlTo, $currentUrl);
        if ($urlToPostfix !== false) {
            return str_replace(self::REDIRECT_CHAIN, $urlToPostfix, $urlTo);
        }

        return false;
    }

    /**
     * @param string $urlFrom
     * @param string $urlTo
     * @param string $currentUrl
     *
     * @return bool|string
     */
    private function getUrlToPostfix($urlFrom, $urlTo, $currentUrl)
    {
        $urlToPostfix  = false;
        $urlToExploded = explode(self::REDIRECT_CHAIN, $urlTo);
        if (strpos($urlFrom, self::REDIRECT_CHAIN) !== false
            && isset($urlToExploded[1]) && !$urlToExploded[1]) {
            $urlFromPrepared = str_replace(self::REDIRECT_CHAIN, '', $urlFrom);
            $urlToPostfix    = str_replace($urlFromPrepared, '', $currentUrl);
        }

        if ((isset($urlToPostfix) && $urlToPostfix
                && strpos($currentUrl, $urlToPostfix) !== false)
            || (isset($urlToPostfix) && $urlToPostfix !== false)) {
            return $urlToPostfix;
        }

        return false;
    }
}
