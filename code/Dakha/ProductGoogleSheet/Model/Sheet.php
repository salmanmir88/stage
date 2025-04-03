<?php
namespace Dakha\ProductGoogleSheet\Model;

class Sheet extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dakha\ProductGoogleSheet\Model\ResourceModel\Sheet');
    }
}
?>