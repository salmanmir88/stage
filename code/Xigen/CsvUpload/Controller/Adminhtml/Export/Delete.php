<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;

class Delete extends Action
{
    protected $_filesystem;
    public function __construct(
        Action\Context $context,
        \Magento\Framework\Filesystem $filesystem
    ) {
        parent::__construct($context);
        $this->_filesystem = $filesystem;
    }

    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $filePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            ->getAbsolutePath('import/outputcsv/' . $file);

        if (file_exists($filePath)) {
            unlink($filePath);
            $this->messageManager->addSuccessMessage(__('File deleted successfully.'));
        } else {
            $this->messageManager->addError(__('File does not exist.'));
        }

        return $this->_redirect('*/*/');
    }
}
