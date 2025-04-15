<?php

namespace Developerswing\OrderTracking\Controller\Adminhtml\Tracking;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Framework\Filesystem;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Spi\OrderResourceInterface;
use Magento\Sales\Api\Data\OrderInterfaceFactory;
use Magento\Sales\Api\Data\OrderInterface;

class Index extends Action
{
    protected $resultPageFactory;
    protected $orderRepository;
    protected $uploaderFactory;
    protected $filesystem;
    protected $directoryList;
    protected $resourceConnection;
    protected $messageManager;
    protected $orderResource;
    protected $orderFactory;

    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        ResourceConnection $resourceConnection,
        OrderResourceInterface $orderResource,
        OrderInterfaceFactory $orderFactory,
        ManagerInterface $messageManager
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->orderRepository = $orderRepository;
        $this->uploaderFactory = $uploaderFactory;
        $this->filesystem = $filesystem;
        $this->directoryList = $directoryList;
        $this->resourceConnection = $resourceConnection;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
        $this->messageManager = $messageManager;
    }

    public function execute()
    {
        if ($this->getRequest()->isPost() && isset($_FILES['tracking_file'])) {
            try {
                $uploader = $this->uploaderFactory->create(['fileId' => 'tracking_file']);
                $uploader->setAllowedExtensions(['csv']);
                $uploader->setAllowRenameFiles(true);
                $uploader->setFilesDispersion(false);

                $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
                $path = $mediaDirectory->getAbsolutePath('import/');
                $result = $uploader->save($path);

                $filePath = $result['path'] . '/' . $result['file'];
                $updateCount = $this->processFile($filePath);

                if ($updateCount > 0) {
                    $this->messageManager->addSuccessMessage(__('Tracking links updated for %1 orders.', $updateCount));
                } else {
                    $this->messageManager->addErrorMessage(__('No tracking links updated. Check CSV file format.'));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Error: %1', $e->getMessage()));
            }

            return $this->resultRedirectFactory->create()->setPath('*/*/');
        }

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Upload Tracking Links'));
        return $resultPage;
    }

    private function processFile($filePath)
    {
        $fileHandle = fopen($filePath, 'r');
        $header = fgetcsv($fileHandle);
        $updateCount = 0;

        while (($row = fgetcsv($fileHandle)) !== false) {
            $data = array_combine($header, $row);

            if (!$data) {
                $this->messageManager->addErrorMessage(__('CSV file format issue: Header mismatch or empty row.'));
                continue;
            }

            $orderIncrementId = $data['order_id'] ?? null;
            $trackingLink = $data['track_link'] ?? null;
            $orderIncrementId = str_pad($orderIncrementId, 9, "0", STR_PAD_LEFT);
            if (!$orderIncrementId || !$trackingLink) {
                $this->messageManager->addErrorMessage(__('Missing order ID or tracking link in CSV.'));
                continue;
            }

            try {
                $order = $this->getOrder($orderIncrementId);
                if ($order && $order->getId()) {
                    $order->setData('tracking_link', $trackingLink);
                    $this->orderRepository->save($order);
                    $updateCount++;
                } else {
                    $this->messageManager->addErrorMessage(__('Order ID %1 not found.', $orderIncrementId));
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    sprintf(__('Error updating Order ID %1: %2'), $orderIncrementId, $e->getMessage())
                );
            }
        }

        fclose($fileHandle);
        return $updateCount;
    }

    public function getOrder($incrementId)
    {
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $incrementId, OrderInterface::INCREMENT_ID);
        return $order->getId() ? $order : null;
    }
}
