<?php

namespace Dakha\ProductGoogleSheet\Model\ResourceModel\Sheet;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Dakha\ProductGoogleSheet\Model\Sheet', 'Dakha\ProductGoogleSheet\Model\ResourceModel\Sheet');
        $this->_map['fields']['page_id'] = 'main_table.page_id';
    }

}
?>