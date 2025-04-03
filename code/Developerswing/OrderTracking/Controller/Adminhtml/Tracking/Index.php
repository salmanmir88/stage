<?php

namespace Developerswing\OrderTracking\Controller\Adminhtml\Tracking;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Controller\ResultFactory;

class Index extends Action
{
    protected $resultPageFactory;
    protected $orderFactory;
    protected $directoryList;
    protected $resourceConnection;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderFactory $orderFactory,
        DirectoryList $directoryList,
        ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderFactory = $orderFactory;
        $this->directoryList = $directoryList;
        $this->resourceConnection = $resourceConnection;
    }

    public function execute()
    {
        // Ensure the tracking_link attribute exists
        $this->ensureTrackingLinkAttribute();

        if ($this->getRequest()->isPost() && $this->getRequest()->getFiles('tracking_file')) {
            try {
                // Handle the file upload
                $uploader = $this->_objectManager->create('Magento\MediaStorage\Model\File\Uploader', ['fileId' => 'tracking_file']);
                $uploader->setAllowedExtensions(['csv']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);
                $path = $this->directoryList->getPath(DirectoryList::VAR_DIR) . '/import/';
                $result = $uploader->save($path);

                $filePath = $result['path'] . '/' . $result['file'];
                $updateCount = $this->processFile($filePath);

                if ($updateCount > 0) {
                    $this->messageManager->addSuccessMessage(__('Tracking links have been updated successfully for %1 orders.', $updateCount));
                } else {
                    $this->messageManager->addErrorMessage(__('No tracking links were updated. Please check the file content.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
            }

            // Redirect to prevent form resubmission
            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        // Render the page
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Tracking Links'));
        return $resultPage;
    }

    private function processFile($filePath)
    {
        $connection = $this->resourceConnection->getConnection();
        $fileHandle = fopen($filePath, 'r');
        $header = fgetcsv($fileHandle); // Read the header row
        $updateCount = 0;

        while (($row = fgetcsv($fileHandle)) !== false) {
            $data = array_combine($header, $row);
            $orderIncrementId = $data['orderid'] ?? null;
            $order = $this->orderFactory->create()->loadByIncrementId($orderIncrementId);
	    $orderId = $order->getId();
            $trackingLink = $data['track_link'] ?? null;
            if ($orderId && $trackingLink) {
                try {
                    $connection->update(
                        $connection->getTableName('sales_order'),
                        ['tracking_link' => $trackingLink],
                        ['entity_id = ?' => $orderId]
                    );
                    $updateCount++;
                } catch (\Exception $e) {
                    $this->messageManager->addErrorMessage(__('Error updating Order ID %1: %2', $orderId, $e->getMessage()));
                }
            }
        }

        fclose($fileHandle);
        return $updateCount;
    }

    private function ensureTrackingLinkAttribute()
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $connection->getTableName('sales_order');

        if (!$connection->tableColumnExists($tableName, 'tracking_link')) {
            $connection->addColumn(
                $tableName,
                'tracking_link',
                [
                    'type' => \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                    'nullable' => true,
                    'comment' => 'Tracking Link'
                ]
            );
        }
    }
}

