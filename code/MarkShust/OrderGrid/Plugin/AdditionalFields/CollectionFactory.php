<?php
namespace MarkShust\OrderGrid\Plugin\AdditionalFields;
class CollectionFactory
{
    private $collection;
    public function aroundGetReport(
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
        \Closure $proceed,
        $requestName
    ){
        $result = $proceed($requestName);
        if ($requestName == 'sales_order_grid_data_source') { // To check is that sales order grid
            if ($result instanceof \Magento\Sales\Model\ResourceModel\Order\Grid\Collection) { // Check that is the object of order grid
                $select = $result->getSelect();
                // Here, join the new field country_id which contain the country code
                $select->join(
                    ["soa" => "sales_order_address"],
                    'main_table.entity_id = soa.parent_id AND soa.address_type="shipping"',
                    array('country_id')
                )->distinct();
                // I have imported country table which contain county code & Name 
                // I join the new field country_name in the grid 
                // this country_name field name you can see on the sales_order_grid both are need to same to workable your filter perfectly
                $select->join(
                    array('cn' => "country"),
                    'soa.country_id = cn.code',
                    array('country_name')
                );
            }
        }
        return $result;
    }
}