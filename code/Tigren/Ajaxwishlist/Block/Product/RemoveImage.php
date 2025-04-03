<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block\Product;

/**
 * Class Image
 * @package Tigren\Ajaxwishlist\Block\Product
 */
class RemoveImage extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $prdImageHelper;

    /**
     * Image constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Catalog\Helper\Image $imageHelper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectManager;
        $this->prdImageHelper = $imageHelper;
    }

    /**
     * @return string
     */
    public function getImageUrl()
    {
        $color = $this->_request->getParam('color');
        $configurablePrdModel = $this->_objectManager->get('Magento\ConfigurableProduct\Model\Product\Type\Configurable');
        $attributeOptions = [93 => $color];
        $prdId = $this->_coreRegistry->registry('current_product')->getId();
        $product = $this->_objectManager->get('Magento\Catalog\Model\Product')->load($prdId);
        $imageUrl = $this->getProductImageUrl($product, 'category');
        return $imageUrl;
    }

    /**
     * @param $product
     * @param $size
     * @return string
     */
    public function getProductImageUrl($product, $size)
    {
        $imageSize = 'product_page_image_' . $size;
        if ($size == 'category') {
            $imageSize = 'category_page_list';
        }
        $imageUrl = $this->prdImageHelper->init($product, $imageSize)
            ->keepAspectRatio(true)
            ->keepFrame(false)
            ->getUrl();
        return $imageUrl;
    }
}