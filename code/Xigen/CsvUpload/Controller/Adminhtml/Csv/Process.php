<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Csv;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cron\Model\ScheduleFactory;
use Xigen\CsvUpload\Helper\Csv;
use Xigen\CsvUpload\Model\CsvFactory;

class Process extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $scheduleFactory;
    protected $csvHelper;
    protected $csvFactory;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ScheduleFactory $scheduleFactory,
        Csv $csvHelper,
        CsvFactory $csvFactory
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->csvHelper = $csvHelper;
        $this->csvFactory = $csvFactory;

        parent::__construct($context);
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
        $isLockedToRemove = $this->csvHelper->isAnyCsvFileLockedToRemove();
        $isLockedToAppy = $this->csvHelper->isAnyCsvFileLockedToApply();
        if ($isLockedToAppy || $isLockedToRemove) {
            $this->messageManager->addErrorMessage(__('One Rule is already in process Please try once it is completed'));
            return $resultRedirect->setPath('*/*/');
        } else {
            $isApplied = $this->csvHelper->isAnyCsvFileApplied();
            if ($isApplied) {
                $this->messageManager->addErrorMessage(__('One Rule is already Applied Please check.'));
                return $resultRedirect->setPath('*/*/');
            } else {
                $this->csvHelper->setCsvFileLockedToApplyToId($fileToProcess->getId(), 1);
                $this->scheduleCron();
            }
        }
    }

    public function scheduleCron()
    {
        $cronSchedule = $this->scheduleFactory->create();
        $cronSchedule->setJobCode('apply_special_price_cron');
        $cronSchedule->setScheduledAt(date('Y-m-d H:i:s', time())); // Set to current time
        $cronSchedule->save();

        $this->messageManager->addSuccessMessage(__('Apply action scheduled successfully.'));
        return $this->_redirect('*/*/');
    }
}
