<?php
namespace Evince\CourierManager\Plugin\Checkout;

class LayoutProcessor 
{
    protected $courierModelFactory;

    public function __construct
    (
        \Evince\CourierManager\Model\ResourceModel\Grid\CollectionFactory $courierModelFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) 
    {
        $this->courierModelFactory = $courierModelFactory;
        $this->_storeManager = $storeManager;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param array $jsLayout
     * @return array
     */
    public function afterProcess(
    \Magento\Checkout\Block\Checkout\LayoutProcessor $subject, array $jsLayout
    ) {

        $jsLayout['components']['checkout']['children']['steps']['children']['shipping-step']['children']
                ['shippingAddress']['children']['shipping-address-fieldset']['children']['city'] = [
            'component' => 'Evince_CourierManager/js/form/element/city',
            'config' => [
                'customScope' => 'shippingAddress.city',
                'customEntry' => null,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'city',
            ],
            'dataScope' => 'shippingAddress.city',
            'label' => __('City'),
            'provider' => 'checkoutProvider',
            /*'filterBy' => [
                'target' => '${ $.provider }:shippingAddress.country_id',
                'field' => 'country_id'
            ],*/
            'options' => $this->getSaudiCityList(),
            'visible' => true,
            'validation' => ['required-entry' => true],
            'sortOrder' => 45,
            'id' => 'city',
        ];

        return $jsLayout;
    }

    public function getSaudiCityList()
    {
        $param = 'SA';
        $collection = $this->courierModelFactory->create();
        $collection->addFieldToFilter('country_code',  array('eq'=>$param));
        $collection->addFieldToFilter('store_ids',  array('eq'=>$this->_storeManager->getStore()->getId()));
        $cities = [];
        foreach($collection as $city)
        {
            if($this->_storeManager->getStore()->getId()=='2')
             {
                $cities[] = ['value'=>$city->getCityArabic(),'label' => $city->getCityArabic()]; 
             }
             else
             {
                $cities[] = ['value'=>$city->getCity(),'label' => $city->getCity()];    
             }
        }
        if(empty($cities))
        {
            $cities[] = ['code' => '', 'name' => __('Please Select')];
        }

        $arrayName = $cities;
        return $arrayName;

    }

}
