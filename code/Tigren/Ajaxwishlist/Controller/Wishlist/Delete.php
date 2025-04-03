<?php

namespace Tigren\Ajaxwishlist\Controller\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Exception\NotFoundException;
use Tigren\Ajaxwishlist\Helper\Data as AjaxwishlistData;

/**
 * Class Delete
 * @package Tigren\Ajaxwishlist\Controller\Wishlist
 */
class Delete extends \Magento\Wishlist\Controller\AbstractIndex
{
    /**
     * @var \Magento\Wishlist\Model\Wishlist
     */
    protected $_wishlist;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var AjaxwishlistData
     */
    protected $_ajaxWishlistHelper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * Delete constructor.
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param AjaxwishlistData $ajaxWishlistHelper
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Json\Helper\Data $jsonEncode
     * @param \Magento\Wishlist\Model\Wishlist $wishlist
     */
    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        AjaxwishlistData $ajaxWishlistHelper,
        \Magento\Framework\Registry $registry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonEncode,
        \Magento\Wishlist\Model\Wishlist $wishlist
    ) {
        $this->_wishlist = $wishlist;
        $this->_ajaxWishlistHelper = $ajaxWishlistHelper;
        $this->_jsonEncode = $jsonEncode;
        $this->_storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    /**
     *
     */
    public function execute()
    {
        $request = $this->getRequest();
        $product_id = $request->getParam('product', null);
        if (!$product_id) {
            throw new NotFoundException(__('Page not found'));
        }

        $customer_id = $request->getParam('customerId', null);
        $wishlist = $this->_wishlist->loadByCustomerId($customer_id);
        if (!$wishlist) {
            throw new NotFoundException(__('Page not found'));
        }

        $items = $wishlist->getItemCollection();
        foreach ($items as $item) {
            if ($item->getProductId() == $product_id) {
                try {
                    $item->delete();
                    $wishlist->save();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError(
                        __('We can\'t delete the item from Wish List right now because of an error: %1.',
                            $e->getMessage())
                    );
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('We can\'t delete the item from the Wish List right now.'));
                }
            }
        }
        if (!empty($this->getRequest()->getParam('isRemoveSubmit', null))) {
            $product = $this->_initProduct();
            $this->_coreRegistry->register('product', $product);
            $this->_coreRegistry->register('current_product', $product);
            $htmlPopup = $this->_ajaxWishlistHelper->getSuccessRemoveHtml();
            $result['success'] = true;
            $result['html_popup'] = $htmlPopup;
            $this->getResponse()->representJson($this->_jsonEncode->jsonEncode($result));
        }
        $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class)->calculate();
    }

    /**
     * @return bool|\Magento\Catalog\Api\Data\ProductInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _initProduct()
    {
        $productId = (int)$this->getRequest()->getParam('product', null);
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