<?php

namespace Evince\CourierManager\Model\ResourceModel;

class Grid extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{

    protected $_idFieldName = 'entity_id';

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        $resourcePrefix = null
    ) {
        parent::__construct($context, $resourcePrefix);
        
    }
    
    protected function _construct()
    {
        $this->_init('courier_manager', 'entity_id');
    }
}
