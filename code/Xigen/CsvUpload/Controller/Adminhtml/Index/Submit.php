<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Index;

/**
 * Submit controller class
 */
class Submit extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * Constructor
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute view action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Xigen_CsvUpload::csv');
        $resultPage->setActiveMenu('Xigen_CsvUpload::index_submit');
        $resultPage->getConfig()->getTitle()->prepend(__("Create New Special Price Rule"));
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
        return $this->_authorization->isAllowed('Xigen_CsvUpload::index_submit');
    }
}
