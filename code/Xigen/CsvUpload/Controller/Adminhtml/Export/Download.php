<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;

class Download extends Action
{
    protected $fileFactory;
    protected $_filesystem;

    public function __construct(Action\Context $context, FileFactory $fileFactory, \Magento\Framework\Filesystem $filesystem)
    {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
    }

    public function execute()
    {
        $file = $this->getRequest()->getParam('file');
        $filePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR)
            ->getAbsolutePath('import/outputcsv/' . $file);

        if (file_exists($filePath)) {
            return $this->fileFactory->create(
                basename($filePath),
                ['type' => 'filename', 'value' => $filePath, 'rm' => false],
                \Magento\Framework\App\Filesystem\DirectoryList::VAR_DIR
            );
        } else {
            $this->messageManager->addError(__('File does not exist.'));
            return $this->_redirect('*/*/');
        }
    }
}
