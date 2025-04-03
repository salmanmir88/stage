<?php

namespace Developerswing\OrderTracking\Model\ResourceModel\Orderstatusdate;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Developerswing\OrderTracking\Model\Orderstatusdate', 'Developerswing\OrderTracking\Model\ResourceModel\Orderstatusdate');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>