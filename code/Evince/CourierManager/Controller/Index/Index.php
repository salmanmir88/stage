<?php

namespace Evince\CourierManager\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

class Index extends \Magento\Framework\App\Action\Action {

    protected $request;
    protected $courierModelFactory;
    protected $json;
    protected $_storeManager;


    public function __construct(
        Context $context,
        \Magento\Framework\App\RequestInterface $request,
        \Evince\CourierManager\Model\ResourceModel\Grid\CollectionFactory $courierModelFactory,
        \Magento\Framework\Serialize\Serializer\Json $json,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->request = $request;
        $this->courierModelFactory = $courierModelFactory;
        $this->json = $json;
        $this->_storeManager = $storeManager;
        parent::__construct($context);
    }

    public function execute() {
        /* Create array for return value */
        if(isset($_GET['country_code']))
        {
            $param = $_GET['country_code'];
            $collection = $this->courierModelFactory->create();
            $collection->addFieldToFilter('country_code',  array('eq'=>$param));
            $collection->addFieldToFilter('store_ids',  array('eq'=>$this->_storeManager->getStore()->getId()));
            $response =[];
            foreach ($collection as $city)
            {
                if($this->_storeManager->getStore()->getId()=='2')
                {
                   $response[] = array('code'=>$city->getCityArabic(),'name' => $city->getCityArabic()); 
                }
                else
                {
                    $response[] = array('code'=>$city->getCity(),'name' => $city->getCity());    
                }
                
            }

            $jsonEncode = json_encode($response);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);  //create Json type return object
            $resultJson->setData($jsonEncode);  // array value set in Json Result Data set
        }
        else
        {
            $response = [];
            $response[] = array('code' => '', 'name' => __('Please Select'));
            $jsonEncode = json_encode($response);
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);  //create Json type return object
            $resultJson->setData($jsonEncode);  // array value set in Json Result Data set
        }
        
        return $resultJson; // return json object
    }

}

?>