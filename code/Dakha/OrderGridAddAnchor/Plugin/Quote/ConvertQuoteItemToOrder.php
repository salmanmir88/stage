<?php

namespace Dakha\OrderGridAddAnchor\Plugin\Quote;

class ConvertQuoteItemToOrder{

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productloader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * PlaceOrder constructor.
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Catalog\Model\ProductFactory $_productloader
    )
    {
        $this->_productloader = $_productloader;
        $this->logger  = $logger;
    }

	public function aroundConvert(
       \Magento\Quote\Model\Quote\Item\ToOrderItem $subject,
       \Closure $proceed,
       \Magento\Quote\Model\Quote\Item\AbstractItem $item,
       $additional = []
   ) {
       try {
            $orderItem = $proceed($item, $additional);
            $_product = $this->_productloader->create()->load($orderItem->getProductId());

            $catIds = ['3','10','11','12','13','14','15','16','17','18','19','20'];
            $isTrue = false;
            foreach($_product->getCategoryIds() as $catId)
            {
                if(in_array($catId, $catIds))
                {
                   $isTrue = true;
                   break;
                } 
            }
            
            if($isTrue){
            $orderItem->setProductCommonCode('PT4KPOP');
            }
            
            $orderItem->setProductModel($_product->getProductModel());
            $orderItem->setVersion($_product->getVersion());
            $orderItem->setAlbumQyt($_product->getAlbumQyt());
            $orderItem->setIsFeatured($_product->getIsFeatured());
            $orderItem->setUpc($_product->getUpc());
            $orderItem->setLink($_product->getLink());
            $orderItem->setBarcode($_product->getBarcode());
            return $orderItem;  
        } catch (Exception $e) {
            $this->logger->info($e->getMessage());
        } 
   }
}