<?php
namespace Eextensions\CustomOrderTab\Model;

class Comment extends \Magento\Framework\Model\AbstractModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eextensions\CustomOrderTab\Model\ResourceModel\Comment');
    }

}
