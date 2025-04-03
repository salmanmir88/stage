<?php

namespace Evince\OrderHistory\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper {

    protected $product;
    protected $catalogImage;
    protected $items;
    protected $timezone;
    protected $date;
    protected $_productRepositoryFactory;

    public function __construct(
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Helper\Image $catalogImage,
        \Magento\Sales\Model\Order\Item $items,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Api\ProductRepositoryInterfaceFactory $productRepositoryFactory
    ) {
        $this->product = $product;
        $this->catalogImage = $catalogImage;
        $this->items = $items;
        $this->timezone = $timezone;
        $this->date = $date;
        $this->_productRepositoryFactory = $productRepositoryFactory;
    }
    
    public function getOrderItemImage($productId) {
        $product = $this->_productRepositoryFactory->create()->getById($productId);
        return $product;
//        $_product = $this->product->load($productId);
//        $image_url = $this->catalogImage->init($_product, 'product_small_image')
//            ->constrainOnly(TRUE)
//            ->keepAspectRatio(TRUE)
//            ->keepTransparency(TRUE)
//            ->keepFrame(FALSE)
//            ->resize(200, 300);
//        return $image_url->getUrl();
        
    }
    
    public function getSelectedOptions($item) {
        $result = [];
        $options = $item->getProductOptions();
        if ($options) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }
        return $result;
    }
        
    public function getFormatDate($orderDate) {
        $dateFormat = $this->timezone->date(new \DateTime($orderDate))->format('F j, Y');
        return $dateFormat;
    }
    
    public function getExpectedDeliveryDate($orderPlaceDate)
    {
        $date = $this->date->date('Y-m-d',$orderPlaceDate); // current date
        $nextdate = $this->date->date('Y-m-d',strtotime($date." +7 days")); //next day date
        $_dateFormat = $this->timezone->date(new \DateTime($nextdate))->format('M j, Y');
        return $_dateFormat;
    }

}
