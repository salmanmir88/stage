<?php
namespace Developerswing\OrderTracking\Model\ResourceModel;

class Orderstatusdate extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('track_order_status_dates', 'id');
    }
}
?>