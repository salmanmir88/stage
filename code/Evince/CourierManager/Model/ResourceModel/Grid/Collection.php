<?php

namespace Evince\CourierManager\Model\ResourceModel\Grid;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    
    protected $_idFieldName = 'entity_id';
    
    protected function _construct()
    {
        $this->_init(
            'Evince\CourierManager\Model\Grid',
            'Evince\CourierManager\Model\ResourceModel\Grid'
        );
    }
}
