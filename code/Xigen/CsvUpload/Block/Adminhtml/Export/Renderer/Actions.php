<?php

namespace Xigen\CsvUpload\Block\Adminhtml\Export\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Framework\DataObject;

class Actions extends AbstractRenderer
{
    public function render(\Magento\Framework\DataObject $row)
    {
        $fileName = $row->getFileName();
        $downloadUrl = $this->getUrl('xigen_csvupload/export/download', ['file' => $fileName]);
        $deleteUrl = $this->getUrl('xigen_csvupload/export/delete', ['file' => $fileName]);

        return sprintf(
            '<a href="%s">Download</a> | <a href="%s" onclick="return confirm(\'Are you sure?\')">Delete</a>',
            $downloadUrl,
            $deleteUrl
        );
    }
}
