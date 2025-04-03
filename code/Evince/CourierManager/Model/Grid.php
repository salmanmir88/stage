<?php
namespace Evince\CourierManager\Model;

class Grid extends \Magento\Framework\Model\AbstractModel
{
    protected function _construct()
    {
        $this->_init('Evince\CourierManager\Model\ResourceModel\Grid');
    }
}
