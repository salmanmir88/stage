<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Index;

/**
 * Process controller class
 */
class Process extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    private $uploaderFactory;

    /**
     * @var \Magento\Framework\Image\AdapterFactory
     */
    private $adapterFactory;

    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface
     */
    private $mediaDirectory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Session\Generic
     */
    private $generic;

    /**
     * @var \Xigen\CsvUpload\Model\CsvFactory
     */
    private $csvFactory;

    /**
     * Process constructor.
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory
     * @param \Magento\Framework\Image\AdapterFactory $adapterFactory
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Session\Generic $generic
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Xigen\CsvUpload\Model\CsvFactory $csvFactory
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\MediaStorage\Model\File\UploaderFactory $uploaderFactory,
        \Magento\Framework\Image\AdapterFactory $adapterFactory,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Session\Generic $generic,
        \Psr\Log\LoggerInterface $logger,
        \Xigen\CsvUpload\Model\CsvFactory $csvFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->jsonHelper = $jsonHelper;
        $this->uploaderFactory = $uploaderFactory;
        $this->adapterFactory = $adapterFactory;
        $this->filesystem = $filesystem;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->generic = $generic;
        $this->csvFactory = $csvFactory;

        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = [];

        try {
            $target = $this->mediaDirectory->getAbsolutePath('csv/' . $this->generic->getSessionId() . '/');
            $ruleName = $this->getRequest()->getParam('rule_name');
            $uploader = $this->uploaderFactory->create(['fileId' => 'import_csv_file']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setAllowRenameFiles(true);

            // Get uploaded file information
            $fileData = $uploader->validateFile();

            // Get the file name from the uploaded file info
            $str = $fileData['name']; // The key 'name' contains the uploaded file name;
            $str = $this->getCorrectFileName($str);

            // Get the current date and time
            $currentDateTime = date('Y-m-d_H-i-s');

            // Ensure original filename is preserved and append timestamp without removing any characters
            $newFileName = pathinfo($str, PATHINFO_FILENAME) . '_' . $currentDateTime . '.csv'; // Use underscore to separate parts

            // Save the file
            $result = $uploader->save($target, $newFileName);

            if ($result['file']) {
                $mediaUrl = $this->storeManager->getStore()
                    ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);

                $message = __("File has been successfully uploaded");

                $data = [
                    'filename' => $newFileName,
                    'path' => $mediaUrl . 'csv/' . $this->generic->getSessionId() . '/' . $newFileName,
                ];

                $file = $this->csvFactory->create();
                $file->setFilename($data['path']);
                $file->setRulename($ruleName);
                $file->save();

                $this->messageManager->addSuccess(__($message));
            }
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $this->messageManager->addError(__($message));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('*/csv/');
        return $resultRedirect;
    }

    public function getCorrectFileName($fileName)
    {
        $fileName = $fileName !== null ? ltrim($fileName, '.') : '';
        $fileName = preg_replace('/[^a-z0-9_\\-\\.]+/i', '_', $fileName);
        $fileInfo = pathinfo($fileName);
        $fileInfo['extension'] = $fileInfo['extension'] ?? '';

        if (preg_match('/^_+$/', $fileInfo['filename'] ?? '')) {
            $fileName = 'file.' . $fileInfo['extension'];
        }

        return $fileName;
    }
}

