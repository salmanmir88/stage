<?php

namespace Dakha\OrderGridAddAnchor\Plugin;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResourceConnection;

class OrdersGrid
{
    protected $request;
    protected $resourceConnection;

    public function __construct(
        RequestInterface $request,
        ResourceConnection $resourceConnection
    ) {
        $this->request = $request;
        $this->resourceConnection = $resourceConnection;
    }

    public function afterGetReport($subject, $collection, $requestName)
    {
        $routeName = $this->request->getRouteName();

        if ($routeName !== 'customer' && $collection->getMainTable() === $this->resourceConnection->getTableName('sales_order_grid')) {
            $orderAddressTable = $this->resourceConnection->getTableName('sales_order_address');

            $collection->getSelect()->joinLeft(
                ['oat' => $orderAddressTable],
                'oat.parent_id = main_table.entity_id AND oat.address_type = \'shipping\'',
                ['telephone', 'city', 'postcode', 'street', 'country_id']
            );
        }

        return $collection;
    }
}
