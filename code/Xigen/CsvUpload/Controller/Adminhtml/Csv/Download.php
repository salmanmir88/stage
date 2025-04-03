<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Csv;

use Magento\Backend\App\Action;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Store\Model\StoreManagerInterface;
use Xigen\CsvUpload\Model\CsvFactory;

class Download extends Action
{
    protected $fileFactory;
    protected $_filesystem;
    protected $resourceConnection;
    protected $storeManager;
    protected $csvFactory;

    public function __construct(
        Action\Context $context,
        FileFactory $fileFactory,
        \Magento\Framework\Filesystem $filesystem,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        CsvFactory $csvFactory
    ) {
        parent::__construct($context);
        $this->fileFactory = $fileFactory;
        $this->_filesystem = $filesystem;
        $this->resourceConnection = $resourceConnection;
        $this->storeManager = $storeManager;
        $this->csvFactory = $csvFactory;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('csv_id');

        $fileToProcess = $this->csvFactory->create();
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect * */
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($id) {
            $fileToProcess->load($id);
            if (!$fileToProcess->getId()) {
                $this->messageManager->addErrorMessage(__('This Csv no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
        }
        $filenameUrl = $fileToProcess->getData('filename');
        $filePath = $this->getFileName($filenameUrl);
        if (file_exists($filePath)) {
            return $this->fileFactory->create(
                basename($filePath),
                ['type' => 'filename', 'value' => $filePath, 'rm' => false],
                \Magento\Framework\App\Filesystem\DirectoryList::PUB
            );
        } else {
            $this->messageManager->addError(__('File does not exist.'));
            return $this->_redirect('*/*/');
        }
    }

    public function getFileName($filenameUrl)
    {
        $filePath = $this->_filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::PUB)
            ->getAbsolutePath();
        // Extract the path from the URL
        $fileUrlPath = parse_url($filenameUrl, PHP_URL_PATH);
        //     // Combine both paths
        $finalFilePath = rtrim($filePath, '/') . $fileUrlPath;

        return $finalFilePath;
    }
}
