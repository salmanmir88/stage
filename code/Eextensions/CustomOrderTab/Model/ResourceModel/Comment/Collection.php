<?php

namespace Eextensions\CustomOrderTab\Model\ResourceModel\Comment;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Eextensions\CustomOrderTab\Model\Comment', 'Eextensions\CustomOrderTab\Model\ResourceModel\Comment');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
