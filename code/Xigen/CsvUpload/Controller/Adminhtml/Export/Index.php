<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Export;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Index
 *
 * @package Xigen\CsvUpload\Controller\Adminhtml\Import
 */
class Index extends \Magento\ImportExport\Controller\Adminhtml\Import\Index
{
    /**
     * @var \Magento\ImportExport\Helper\Data
     */
    protected $helperData;

    /**
     * Index constructor.
     * @param Context $context
     * @param \Magento\ImportExport\Helper\Data $helperData
     */
    public function __construct(Context $context, \Magento\ImportExport\Helper\Data $helperData)
    {
        $this->helperData = $helperData;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Page | \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Xigen_CsvUpload::csv');
        $resultPage->setActiveMenu('Xigen_CsvUpload::xigen_csvexport_csv');
        $resultPage->getConfig()->getTitle()->prepend(__('Import/Export'));
        $resultPage->getConfig()->getTitle()->prepend(__('Export SKU From Barcode'));
        $resultPage->addBreadcrumb(__('Import'), __('Export SKU From Barcode'));
        return $resultPage;
    }

    /**
     * Is allowed
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        $this->_authorization->isAllowed('Xigen_CsvUpload::csv');
        return $this->_authorization->isAllowed('Xigen_CsvUpload::xigen_csvexport_csv');
    }
}
