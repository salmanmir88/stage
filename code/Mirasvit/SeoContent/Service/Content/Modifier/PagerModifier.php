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



namespace Mirasvit\SeoContent\Service\Content\Modifier;

use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Seo\Api\Service\StateServiceInterface;
use Mirasvit\SeoContent\Api\Data\ContentInterface;
use Mirasvit\SeoContent\Model\Config;

/**
 * Purpose: Add current page to meta
 */
class PagerModifier implements ModifierInterface
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var StateServiceInterface
     */
    private $stateService;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * PagerModifier constructor.
     * @param Config $config
     * @param StateServiceInterface $stateService
     * @param RequestInterface $request
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        StateServiceInterface $stateService,
        RequestInterface $request,
        StoreManagerInterface $storeManager
    ) {
        $this->config       = $config;
        $this->stateService = $stateService;
        $this->request      = $request;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ContentInterface $content
     * @return ContentInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function modify(ContentInterface $content)
    {
        $store = $this->storeManager->getStore();
        $page  = (int)$this->request->getParam('p', 1);

        if (!$this->stateService->isCategoryPage()) {
            return $content;
        }

        if (!$content->getMetaTitle()) {
            return $content;
        }

        switch ($this->config->getMetaTitlePageNumber($store)) {
            case Config::PAGE_NUMBER_POSITION_AT_BEGIN:
                if ($page > 1) {
                    $content->setMetaTitle(__('Page %1 | %2', $page, $content->getMetaTitle()));
                }
                break;

            case Config::PAGE_NUMBER_POSITION_AT_END:
                if ($page > 1) {
                    $content->setMetaTitle(__('%1 | Page %2', $content->getMetaTitle(), $page));
                }
                break;

            case Config::PAGE_NUMBER_POSITION_AT_BEGIN_WITH_FIRST:
                $content->setMetaTitle(__('Page %1 | %2', $page, $content->getMetaTitle()));
                break;

            case Config::PAGE_NUMBER_POSITION_AT_END_WITH_FIRST:
                $content->setMetaTitle(__('%1 | Page %2', $content->getMetaTitle(), $page));
                break;
        }

        switch ($this->config->getMetaDescriptionPageNumber($store)) {
            case Config::PAGE_NUMBER_POSITION_AT_BEGIN:
                if ($page > 1) {
                    $content->setMetaDescription(__('Page %1 | %2', $page, $content->getMetaDescription()));
                }
                break;

            case Config::PAGE_NUMBER_POSITION_AT_END:
                if ($page > 1) {
                    $content->setMetaDescription(__('%1 | Page %2', $content->getMetaDescription(), $page));
                }
                break;

            case Config::PAGE_NUMBER_POSITION_AT_BEGIN_WITH_FIRST:
                $content->setMetaDescription(__('Page %1 | %2', $page, $content->getMetaDescription()));
                break;

            case Config::PAGE_NUMBER_POSITION_AT_END_WITH_FIRST:
                $content->setMetaDescription(__('%1 | Page %2', $content->getMetaDescription(), $page));
                break;
        }

        return $content;
    }
}
