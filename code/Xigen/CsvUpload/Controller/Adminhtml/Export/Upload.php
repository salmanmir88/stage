<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Message\ManagerInterface;

class Upload extends Action
{
    protected $filesystem;
    protected $dataPersistor;
    protected $uploaderFactory;
    protected $messageManager;

    public function __construct(
        Action\Context $context,
        Filesystem $filesystem,
        UploaderFactory $uploaderFactory,
        ManagerInterface $messageManager,
        DataPersistorInterface $dataPersistor
    ) {
        parent::__construct($context);
        $this->filesystem = $filesystem;
        $this->uploaderFactory = $uploaderFactory;
        $this->messageManager = $messageManager;
        $this->dataPersistor = $dataPersistor;
    }

    public function execute()
    {
        try {
            $uploader = $this->uploaderFactory->create(['fileId' => 'csv_file']);
            $uploader->setAllowedExtensions(['csv']);
            $uploader->setFilesDispersion(false);

            // Save uploaded file to var/import
            $inputDirectory = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath() . '/import/barcodecustomcsv/';
            if (!is_dir($inputDirectory)) {
                mkdir($inputDirectory, 0777, true);
            }
            $result = $uploader->save($inputDirectory);

            
            if($result){
            // Set success message for added to queue
            $this->messageManager->addSuccessMessage(__('CSV file uploaded and added to the queue. Please ensure the cron job is running to process the file.'));
            }else{
                $this->messageManager->addSuccessMessage(__('Some error occure while uploading the CSV file please recheck.'));
            }
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage(__('File upload error: %1', $e->getMessage()));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('An error occurred during file upload: %1', $e->getMessage()));
        }

        return $this->_redirect('*/*/');
    }
}
