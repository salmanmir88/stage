<?php
namespace Eextensions\CustomOrderTab\Model\ResourceModel;

class Comment extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('eextensions_order_custom_comment', 'id');
    }
}
