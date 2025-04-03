<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Csv;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Cron\Model\ScheduleFactory;
use Xigen\CsvUpload\Helper\Csv;


class Remove extends \Magento\Backend\App\Action
{
    protected $jsonFactory;
    protected $scheduleFactory;
    protected $csvHelper;

    public function __construct(
        Context $context,
        JsonFactory $jsonFactory,
        ScheduleFactory $scheduleFactory,
        Csv $csvHelper
    ) {
        $this->jsonFactory = $jsonFactory;
        $this->scheduleFactory = $scheduleFactory;
        $this->csvHelper = $csvHelper;
        parent::__construct($context);
    }

    public function execute()
    {
        // $result = $this->jsonFactory->create();
        $isLockedToApply = $this->csvHelper->isAnyCsvFileLockedToApply();
        $isApplied = $this->csvHelper->isAnyCsvFileApplied();
        $isLockedToRemove = $this->csvHelper->isAnyCsvFileLockedToRemove();
        $id = $this->getRequest()->getParam('csv_id');
        if ($isLockedToApply || $isLockedToRemove) {
            $this->messageManager->addErrorMessage(__('Cant perform this operation right now one of the process in the loop try after some time !'));
            return $this->_redirect('*/*/');
        }
        if ($isApplied) {
            $this->csvHelper->setRemoveFileLockedToId($id, 1);
            // Add cron job schedule to apply special price logic
            $cronSchedule = $this->scheduleFactory->create();
            $cronSchedule->setJobCode('remove_special_price_cron');
            $cronSchedule->setScheduledAt(date('Y-m-d H:i:s', time())); // Set to current time
            $cronSchedule->save();
            $this->messageManager->addSuccessMessage(__('Rule Removed Successfully !'));
            return $this->_redirect('*/*/');
        } else {
            $this->messageManager->addErrorMessage(__('Not Found Any Applied Rule !'));
            return $this->_redirect('*/*/');
        }
    }
}