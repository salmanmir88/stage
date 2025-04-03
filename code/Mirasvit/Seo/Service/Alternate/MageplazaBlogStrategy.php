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

use Magento\Framework\App\RequestInterface;

class MageplazaBlogStrategy
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $manager;
    /**
     * @var \Mirasvit\Seo\Api\Service\Alternate\UrlInterface
     */
    private $url;
    /**
     * @var RequestInterface
     */
    protected $request;
    /**
     * @var mixed
     */
    private $helperBlog;

    /**
     * MageplazaBlogStrategy constructor.
     * @param \Magento\Framework\Module\Manager $manager
     * @param \Mirasvit\Seo\Api\Service\Alternate\UrlInterface $url
     * @param RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\Module\Manager $manager,
        \Mirasvit\Seo\Api\Service\Alternate\UrlInterface $url,
        RequestInterface $request
    ) {
        $this->manager      = $manager;
        $this->url          = $url;
        $this->request      = $request;
    }

    /**
     * {@inheritdoc}
     */
    public function getStoreUrls()
    {
        if ($this->manager->isEnabled('Mageplaza_Blog') && class_exists('\Mageplaza\Blog\Helper\Data')) {
            $this->helperBlog = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Mageplaza\Blog\Helper\Data::class
            );
            $id = $this->getRequest()->getParam('id');
            $post = $this->helperBlog->getFactoryByType(\Mageplaza\Blog\Helper\Data::TYPE_POST)->create()->load($id);
            $storeUrls = $this->url->getStoresCurrentUrl();
            $allowedStores = explode(',', $post->getStoreIds());
            
            if (!$storeUrls) {
                return false;
            }
            
            foreach ($storeUrls as $key => $value) {
                if (!in_array($key, $allowedStores)) {
                    unset($storeUrls[$key]);
                }
            }

            return $storeUrls;
        } else {
            return [];
        }
    }

    /**
     * @return RequestInterface
     */
    public function getRequest()
    {
        return $this->request;
    }
}
