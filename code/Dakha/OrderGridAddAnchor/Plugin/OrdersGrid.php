<?php

namespace Dakha\OrderGridAddAnchor\Plugin;

use Magento\Framework\App\RequestInterface;

class OrdersGrid
{
    protected $request;
    public function __construct(RequestInterface $request)
    {
        $this->request = $request;
    }
    public function afterGetReport($subject, $collection, $requestName)
    {
        $routeName = $this->request->getRouteName();
        if ($routeName !== 'customer' && $collection->getMainTable() === $collection->getResource()->getTable('sales_order_grid')) {
            $orderAddressTable  = $collection->getResource()->getTable('sales_order_address');

            $collection->getSelect()->joinLeft(
                ['oat' => $orderAddressTable],
                'oat.parent_id = main_table.entity_id AND oat.address_type = \'shipping\'',
                ['telephone', 'city', 'postcode', 'street', 'country_id']
            );
        }

        return $collection;
    }
}
