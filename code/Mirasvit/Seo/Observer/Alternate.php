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

use Magento\Directory\Helper\Data as Data;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Mirasvit\Seo\Api\Config\AlternateConfigInterface as AlternateConfig;
use Mirasvit\Seo\Api\Service\StateServiceInterface;
use Mirasvit\Seo\Model\Config as Config;

class Alternate implements ObserverInterface
{
    /**
     * @var \Mirasvit\Seo\Model\Config
     */
    protected $config;

    /**
     * @var \Magento\Framework\View\Element\Template\Context
     */
    protected $context;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $request;

    /**
     * @var \Mirasvit\Seo\Api\Config\AlternateConfigInterface
     */
    protected $alternateConfig;

    /**
     * @var \Mirasvit\Seo\Api\Service\Alternate\StrategyInterface
     */
    protected $strategy;

    /**
     * @var \Mirasvit\Seo\Api\Service\Alternate\UrlInterface
     */
    protected $url;

    private   $strategyFactory;

    /**
     * @var StateServiceInterface
     */
    private $stateInterface;

    public function __construct(
        \Mirasvit\Seo\Model\Config $config,
        \Magento\Framework\View\Element\Template\Context $context,
        \Mirasvit\Seo\Api\Config\AlternateConfigInterface $alternateConfig,
        StateServiceInterface $stateService,
        \Mirasvit\Seo\Service\Alternate\StrategyFactory $strategyFactory,
        \Mirasvit\Seo\Api\Service\Alternate\UrlInterface $url
    ) {
        $this->config          = $config;
        $this->context         = $context;
        $this->request         = $context->getRequest();
        $this->alternateConfig = $alternateConfig;
        $this->stateInterface  = $stateService;
        $this->strategyFactory = $strategyFactory;
        $this->url             = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(Observer $observer)
    {

        /** @var \Magento\Framework\App\Request\Http $request */
        $request = $observer->getData('request');

        // $request is empty in 2.3, and set in 2.4
        if (!$request || $request->getFullActionName() !== '__') {
            $this->strategy = $this->strategyFactory->create();

            $this->setupAlternateTag();
        }
    }

    /**
     * @return bool
     */
    public function setupAlternateTag()
    {
        if (!$this->alternateConfig->getAlternateHreflang($this->context
                ->getStoreManager()
                ->getStore()
                ->getStoreId()) || !$this->request) {
            return false;
        }

        if ($this->stateInterface->isNavigationPage() && !$this->stateInterface->isCategoryPage()) {
            return false;
        }

        $storeUrls = $this->strategy->getStoreUrls();

        $this->addLinkAlternate($storeUrls);
    }

    /**
     * Create alternate.
     *
     * @param array $storeUrls
     *
     * @return bool
     */
    public function addLinkAlternate($storeUrls)
    {
        if (!$storeUrls) {
            return false;
        }
        $pageConfig               = $this->context->getPageConfig();
        $type                     = 'alternate';
        $addLocaleCodeAutomatical = $this->alternateConfig->isHreflangLocaleCodeAddAutomatical();
        foreach ($storeUrls as $storeId => $url) {
            $hreflang = false;
            $stores   = $this->url->getStores();

            if (!isset($stores[$storeId])) {
                continue;
            }

            $storeCode = $stores[$storeId]->getConfig(Data::XML_PATH_DEFAULT_LOCALE);

            if ($this->alternateConfig->getAlternateHreflang($storeId) == AlternateConfig::ALTERNATE_CONFIGURABLE) {
                $hreflang = $this->alternateConfig->getAlternateManualConfig($storeId, true);
            }

            if (!$hreflang) {
                $hreflang = ($hreflang = $this->alternateConfig->getHreflangLocaleCode($storeId)) ?
                    substr($storeCode, 0, 2) . '-' . strtoupper($hreflang) :
                    (($addLocaleCodeAutomatical) ? str_replace('_', '-', $storeCode) :
                        substr($storeCode, 0, 2));
            }
            $url = $this->getPreparedTrailingAlternate($url);
            $pageConfig->addRemotePageAsset(
                htmlspecialchars($url),
                $type,
                ['attributes' => ['rel' => $type, 'hreflang' => $hreflang]]
            );
        }

        $this->addXDefault($storeUrls, $type, $pageConfig);

        return true;
    }

    /**
     * @param string $url
     *
     * @return false|string
     */
    protected function getPreparedTrailingAlternate($url)
    {
        if ($this->config->getTrailingSlash() == Config::TRAILING_SLASH
            && substr($url, -1) != '/'
            && strpos($url, '?') === false) {
            $url = $url . '/';
        } elseif ($this->config->getTrailingSlash() == Config::NO_TRAILING_SLASH
            && substr($url, -1) == '/') {
            $url = substr($url, 0, -1);
        }

        return $url;
    }

    /**
     * Create x-default
     *
     * @param array                               $storeUrls
     * @param string                              $type
     * @param \Magento\Framework\View\Page\Config $pageConfig
     *
     * @return bool
     */
    public function addXDefault($storeUrls, $type, $pageConfig)
    {
        $xDefaultUrl = false;
        $store       = $this->context->getStoreManager()->getStore();
        if ($this->alternateConfig->getAlternateHreflang($store) == AlternateConfig::ALTERNATE_CONFIGURABLE) {
            $xDefaultUrl = $this->alternateConfig->getAlternateManualXDefault($storeUrls);
        } elseif ($this->alternateConfig->getXDefault() == AlternateConfig::X_DEFAULT_AUTOMATICALLY) {
            reset($storeUrls);
            $storeIdXDefault = key($storeUrls);
            $xDefaultUrl     = $storeUrls[$storeIdXDefault];
        } elseif ($this->alternateConfig->getXDefault()) {
            $storeIdXDefault = $this->alternateConfig->getXDefault();
            if (isset($storeUrls[$storeIdXDefault])) {
                $xDefaultUrl = $storeUrls[$storeIdXDefault];
            }
        }

        if ($xDefaultUrl) {
            $xDefaultUrl = $this->getPreparedTrailingAlternate($xDefaultUrl);
            $pageConfig->addRemotePageAsset(
                htmlspecialchars($xDefaultUrl),
                $type,
                ['attributes' => ['rel' => $type, 'hreflang' => 'x-default']]
            );
        }

        return true;
    }
}
