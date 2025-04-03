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



namespace Mirasvit\SeoContent\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\SeoContent\Api\Data\RewriteInterface;
use Mirasvit\SeoContent\Api\Repository\RewriteRepositoryInterface;

class RewriteService
{
    /**
     * @var RewriteRepositoryInterface
     */
    /**
     * @var RewriteRepositoryInterface
     */
    /**
     * @var RewriteRepositoryInterface
     */
    private $rewriteRepository;

    /**
     * @var StoreManagerInterface
     */
    /**
     * @var StoreManagerInterface
     */
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private $request;

    /**
     * RewriteService constructor.
     * @param RewriteRepositoryInterface $rewriteRepository
     * @param StoreManagerInterface $storeManager
     * @param RequestInterface $request
     */
    public function __construct(
        RewriteRepositoryInterface $rewriteRepository,
        StoreManagerInterface $storeManager,
        RequestInterface $request
    ) {
        $this->rewriteRepository = $rewriteRepository;
        $this->storeManager      = $storeManager;
        $this->request           = $request;
    }

    /**
     * @param string $url
     * @return bool|RewriteInterface
     */
    public function getRewrite($url)
    {
        if ($url == null) {
            $url = $this->request->getRequestUri();
        }

        $fullActionName = $this->request->getFullActionName();

        $collection = $this->rewriteRepository->getCollection();
        $collection->addFieldToFilter(RewriteInterface::IS_ACTIVE, true)
            ->addStoreFilter($this->storeManager->getStore())
            ->setOrder(RewriteInterface::SORT_ORDER, 'desc');

        foreach ($collection as $rewrite) {
            if ($this->isFollowPattern($url, $rewrite->getUrl()) ||
                $this->isFollowPattern($fullActionName, $rewrite->getUrl())) {
                return $rewrite;
            }
        }

        return false;
    }

    /**
     * @param string $url
     * @param string $pattern
     * @return bool
     */
    private function isFollowPattern($url, $pattern)
    {
        $url     = strtolower($url);
        $pattern = strtolower($pattern);

        $parts = explode('*', $pattern);
        $index = 0;

        $shouldBeFirst = true;

        foreach ($parts as $part) {
            if ($part == '') {
                $shouldBeFirst = false;
                continue;
            }

            $index = strpos($url, $part, $index);

            if ($index === false) {
                return false;
            }

            if ($shouldBeFirst && $index > 0) {
                return false;
            }

            $shouldBeFirst = false;
            $index         += strlen($part);
        }

        if (count($parts) == 1) {
            return $url == $pattern;
        }

        $last = end($parts);
        if ($last == '') {
            return true;
        }

        if (strrpos($url, $last) === false) {
            return false;
        }

        if (strlen($url) - strlen($last) - strrpos($url, $last) > 0) {
            return false;
        }

        return true;
    }
}
