<?php
namespace Xigen\CsvUpload\Block\Adminhtml\Export;

use Magento\Backend\Block\Widget\Grid\Container;

class Index extends Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_export';
        $this->_blockGroup = 'Xigen_CsvUpload';
        $this->_headerText = __('CSV Files');
        parent::_construct();
        $this->removeButton('add');
    }
}
