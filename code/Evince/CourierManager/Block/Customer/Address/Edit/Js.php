<?php

namespace Evince\CourierManager\Block\Customer\Address\Edit;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\LayoutInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;



class Js extends Template
{
    const CUSTOMER_ADDRESS_EDIT_BLOCK_NAME = 'customer_address_edit';

    /**
     * @var LayoutInterface
     */
    private $currentLayout;
    
    protected $resourceConnection;
    protected $serializer;

    public function __construct(
        Template\Context $context,
        ResourceConnection $resourceConnection,
        SerializerInterface $serializer,
        JsonHelper $jsonHelper,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $data = []
    ) {
        parent::__construct($context, $data);
        $this->serializer = $serializer;
        $this->currentLayout = $context->getLayout();
        $this->resourceConnection = $resourceConnection;
        $this->jsonHelper = $jsonHelper;
        $this->_storeManager = $storeManager;
    }

    public function toHtml()
    {

        return parent::_toHtml();
    }

    public function isActive()
    {
        return true;
    }

    public function getCityJson()
    {
        //return $this->regionCityProHelper->getCityJson();
        $connection = $this->resourceConnection->getConnection();
        $table = $connection->getTableName('courier_manager');
        $query = "Select * FROM " . $table." WHERE store_ids = ".$this->_storeManager->getStore()->getId();
        $result = $connection->fetchAll($query);
        
         $return = [];
         
         foreach ($result as $item) 
         {
            $return[$item['country_code']][$item['entity_id']] = ['code' => $item['city_code'],'name' => $item['city'],'country_code' => $item['country_code']];
            //['region_id' => $item['city_code'], 'city_name' => $item['city']];
         }
         
        $cityData = $return;
        $json = $this->jsonHelper->jsonEncode($cityData);
        return $json;
        // return $this->serializer->serialize($return);
    }

    private function getCurrentAddress()
    {
        $customerAddressBlock = $this->currentLayout->getBlock(self::CUSTOMER_ADDRESS_EDIT_BLOCK_NAME);
        return $customerAddressBlock->getAddress();
    }

    public function getCityId()
    {
        $customerAddress = $this->getCurrentAddress();
        if (! $customerAddress->getId()) {
            return 0;
        }

        return $customerAddress->getCustomAttribute('city_id')
            ? $customerAddress->getCustomAttribute('city_id')->getValue()
            : 0;
    }
}
