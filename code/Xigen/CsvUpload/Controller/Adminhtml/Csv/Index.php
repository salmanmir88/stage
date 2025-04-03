<?php

namespace Xigen\CsvUpload\Controller\Adminhtml\Csv;

/**
 * Xigen CsvUpload Index controller class
 */
class Index extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    private $resultPageFactory;

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
     * Index action
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Xigen_CsvUpload::csv');
        $resultPage->setActiveMenu('Xigen_CsvUpload::xigen_csvupload_csv');
        $resultPage->getConfig()->getTitle()->prepend(__("KPOP Special Price Rules"));
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
        return $this->_authorization->isAllowed('Xigen_CsvUpload::xigen_csvupload_csv');
    }
}
