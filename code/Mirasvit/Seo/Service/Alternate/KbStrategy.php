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



namespace Mirasvit\Seo\Service\Alternate;

class KbStrategy
{
    /**
     * @var \Mirasvit\Core\Model\ResourceModel\UrlRewrite\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var \Mirasvit\Seo\Api\Service\Alternate\UrlInterface
     */
    private $url;
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $manager;

    /**
     * KbStrategy constructor.
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Magento\Framework\Registry $registry
     * @param \Mirasvit\Core\Model\ResourceModel\UrlRewrite\CollectionFactory $collectionFactory
     * @param \Mirasvit\Seo\Api\Service\Alternate\UrlInterface $url
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager,
        \Magento\Framework\Registry $registry,
        \Mirasvit\Core\Model\ResourceModel\UrlRewrite\CollectionFactory $collectionFactory,
        \Mirasvit\Seo\Api\Service\Alternate\UrlInterface $url
    ) {
        $this->manager = $manager;
        $this->collectionFactory = $collectionFactory;
        $this->registry = $registry;
        $this->url = $url;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreUrls()
    {
        if (!$this->manager->isEnabled('Mirasvit_Kb')) {
            return [];
        }
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $config = $objectManager->create('Mirasvit\Kb\Model\Config');
        $storeUrls = $this->url->getStoresCurrentUrl();
        $currentKbUrl = $config->getBaseUrl();
        
        if ($storeUrls) {
            foreach ($storeUrls as $storeId => $url) {
                $storeKbUrl = $config->getBaseUrl($storeId);
                if ($storeKbUrl == $currentKbUrl) {
                    continue;
                }

                $type = 'CATEGORY';
                $entity = $this->registry->registry('kb_current_category');
                if (!$entity || !$entity->getId()) {
                    $type = 'ARTICLE';
                    $entity = $this->registry->registry('current_article');
                }
                // skip comments
                if (!$entity) {
                    continue;
                }

                // we need this because different articles on different stores can have the same url
                $collection = $this->collectionFactory->create()
                    ->addFieldToFilter('module', 'KBASE')
                    ->addFieldToFilter('type', $type)
                    ->addFieldToFilter('entity_id', $entity->getId())
                    ->addFieldToFilter('store_id', ['in' => [0, $storeId]]);
                if (!$collection->count()) {
                    unset($storeUrls[$storeId]);
                } else {
                    $storeUrls[$storeId] = str_replace($currentKbUrl, $storeKbUrl, $url);
                }
            }
        }

        return $storeUrls;
    }

    /**
     * @param string $storeUrls
     */
    public function getAlternateUrl($storeUrls)
    {
    }
}
