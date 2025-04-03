<?php
namespace Dakha\ProductGoogleSheet\Model\ResourceModel;

class Sheet extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('product_google_sheets', 'sheet_id');
    }
}
?>