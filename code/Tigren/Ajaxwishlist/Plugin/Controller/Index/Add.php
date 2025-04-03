<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Plugin\Controller\Index;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Tigren\Ajaxwishlist\Helper\Data as AjaxwishlistData;

/**
 * Class Add
 * @package Tigren\Ajaxwishlist\Plugin\Controller\Index
 */
class Add
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonEncode;
    /**
     * @var \Magento\Framework\Controller\Result\RedirectFactory
     */
    protected $resultRedirectFactory;
    /**
     * @var AjaxwishlistData
     */
    protected $_ajaxWishlistHelper;


    /**
     * Add constructor.
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonEncode
     * @param \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory
     * @param AjaxwishlistData $ajaxWishlistHelper
     */
    public function __construct
    (
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonEncode,
        \Magento\Framework\Controller\Result\RedirectFactory $redirectFactory,
        AjaxwishlistData $ajaxWishlistHelper
    ) {
        $this->resultRedirectFactory = $redirectFactory;
        $this->_jsonEncode = $jsonEncode;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->_ajaxWishlistHelper = $ajaxWishlistHelper;
        $this->_coreRegistry = $registry;
    }

    /**
     * @param $subject
     * @param $proceed
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundExecute($subject, $proceed)
    {
        if($this->_ajaxWishlistHelper->isWishlistPlusEnabled()) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $wishlistPlusHelper = $objectManager->get('Tigren\WishlistPlus\Helper\Data');
            if($wishlistPlusHelper->isWishlistPlusEnable()) {
                $proceed();
            }else{
                $result = [];
                $params = $subject->getRequest()->getParams();

                $product = $this->_initProduct($subject);
                if (!empty($params['isWishlistSubmit'])) {
                    $proceed();
                    $this->_coreRegistry->register('product', $product);
                    $this->_coreRegistry->register('current_product', $product);

                    $htmlPopup = $this->_ajaxWishlistHelper->getSuccessHtml();
                    $result['success'] = true;
                    $result['html_popup'] = $htmlPopup;

                    $subject->getResponse()->representJson($this->_jsonEncode->jsonEncode($result));
                } else {
                    $proceed();
                    return $this->resultRedirectFactory->create()->setPath('*');
                }
            }
        }else{
            $result = [];
            $params = $subject->getRequest()->getParams();

            $product = $this->_initProduct($subject);

            if (!empty($params['isWishlistSubmit'])) {
                $proceed();
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxWishlistHelper->getSuccessHtml();
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;

                $subject->getResponse()->representJson($this->_jsonEncode->jsonEncode($result));
            } else {
                $proceed();
                return $this->resultRedirectFactory->create()->setPath('*');
            }
        }
    }

    /**
     * @param $subject
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     * @throws NoSuchEntityException
     */
    protected function _initProduct($subject)
    {
        $productId = (int)$subject->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);

                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
