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

use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\Seo\Api\Service\StateServiceInterface;
use Mirasvit\SeoContent\Api\Data\ContentInterface;
use Mirasvit\SeoContent\Model\Config;

class MaxLengthModifier implements ModifierInterface
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
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * MaxLengthModifier constructor.
     * @param Config $config
     * @param StateServiceInterface $stateService
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Config $config,
        StateServiceInterface $stateService,
        StoreManagerInterface $storeManager
    ) {
        $this->config       = $config;
        $this->stateService = $stateService;
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

        if ($length = $this->config->getMetaTitleLength($store)) {
            $content->setMetaTitle($this->truncate($content->getMetaTitle(), $length));
        }

        if ($length = $this->config->getMetaDescriptionLength($store)) {
            $content->setMetaDescription($this->truncate($content->getMetaDescription(), $length));
        }

        if ($this->stateService->isProductPage()) {
            if ($length = $this->config->getProductNameLength($store)) {
                $content->setTitle($this->truncate($content->getTitle(), $length));
            }

            if ($length = $this->config->getProductShortDescriptionLength($store)) {
                $content->setShortDescription($this->truncate($content->getShortDescription(), $length));
            }
        }

        return $content;
    }

    /**
     * @param string $string
     * @param int    $limit
     * @return string
     */
    private function truncate($string, $limit)
    {
        return mb_substr($string, 0, $limit);
    }
}
