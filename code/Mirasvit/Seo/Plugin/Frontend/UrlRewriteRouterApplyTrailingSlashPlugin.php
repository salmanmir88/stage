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



namespace Mirasvit\Seo\Plugin\Frontend;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\UrlRewrite\Controller\Router;
use Magento\UrlRewrite\Model\UrlFinderInterface;
use Magento\UrlRewrite\Model\UrlRewrite;
use Mirasvit\Seo\Model\Config;
use Mirasvit\Seo\Helper\Redirect as RedirectHelper;

/**
 * @see \Magento\UrlRewrite\Controller\Router::match();
 */
class UrlRewriteRouterApplyTrailingSlashPlugin
{
    /**
     * @var RedirectHelper
     */
    private $redirectHelper;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var UrlRewrite
     */
    private $urlRewrite;

    /**
     * @var UrlFinderInterface
     */
    private $urlFinder;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * UrlRewriteRouterApplyTrailingSlashPlugin constructor.
     *
     * @param Config                $config
     * @param UrlRewrite            $urlRewrite
     * @param UrlFinderInterface    $urlFinder
     * @param StoreManagerInterface $storeManager
     * @param RedirectHelper        $redirectHelper
     */
    public function __construct(
        Config $config,
        UrlRewrite $urlRewrite,
        UrlFinderInterface $urlFinder,
        StoreManagerInterface $storeManager,
        RedirectHelper $redirectHelper
    ) {
        $this->config         = $config;
        $this->urlRewrite     = $urlRewrite;
        $this->urlFinder      = $urlFinder;
        $this->storeManager   = $storeManager;
        $this->redirectHelper = $redirectHelper;
    }

    /**
     * @param Router           $subject
     * @param callable         $proceed
     * @param RequestInterface $request
     *
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function aroundMatch(Router $subject, callable $proceed, RequestInterface $request)
    {
        $storeId           = $this->storeManager->getStore()->getId();
        $rewriteUrl        = trim($request->getPathInfo(), '/');

        if ($this->config->getTrailingSlash() == Config::TRAILING_SLASH_DISABLE) {
            return $proceed($request);
        } elseif ($this->config->getTrailingSlash() == Config::TRAILING_SLASH) {
            $rewriteUrl .= '/';
        }

        $rewrite = $this->getRewrite($rewriteUrl, $storeId);

        if ($this->checkIfRewriteIdExists($rewrite)) {
            return $proceed($request);
        } else {
            $this->updateUrlRewrite($request, $rewrite, $rewriteUrl, $storeId);
        }

        return $proceed($request);
    }

    /**
     * @param \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null $rewrite
     *
     * @return bool
     */
    private function checkIfRewriteIdExists($rewrite)
    {
        if (!empty($rewrite) && $rewrite->getUrlRewriteId()) {
            return true;
        }
    }

    /**
     * @param RequestInterface $request
     * @param UrlRewrite       $rewrite
     * @param string           $rewriteUrl
     * @param int              $storeId
     */
    private function updateUrlRewrite(RequestInterface $request, $rewrite, string $rewriteUrl, int $storeId)
    {
        if (!$rewrite) {
            $rewrite = $this->getRewrite($rewriteUrl, $storeId);
        }

        if (!$this->checkIfRewriteIdExists($rewrite)) {
           if (substr($rewriteUrl, -1) != '/') {
               $rewriteUrl .= '/';
           } else {
               $rewriteUrl = trim($rewriteUrl, '/');
           }
        }

        $rewrite = $this->getRewrite($rewriteUrl, $storeId);

        if ($this->checkIfRewriteIdExists($rewrite)) {
            $requestPath = $this->redirectHelper->getUrlWithCorrectEndSlash($request->getPathInfo());
            $requestPath = ltrim($requestPath, '/');
            try {
                $rewrite = $this->urlRewrite
                    ->setStoreId($storeId)
                    ->load($rewrite->getUrlRewriteId())
                    ->setData('request_path', $requestPath)
                    ->save();
            } catch (\Exception $e) {}
        }
    }

    /**
     * @param string   $requestPath
     * @param int|null $storeId
     *
     * @return \Magento\UrlRewrite\Service\V1\Data\UrlRewrite|null
     */
    protected function getRewrite($requestPath, $storeId)
    {
        return $this->urlFinder->findOneByData(
            [
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REQUEST_PATH => ltrim($requestPath, '/'),
                \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID     => $storeId,
            ]
        );
    }
}
