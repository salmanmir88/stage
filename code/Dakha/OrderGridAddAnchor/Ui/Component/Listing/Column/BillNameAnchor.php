<?php

namespace Dakha\OrderGridAddAnchor\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\UrlInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;

class BillNameAnchor extends Column
{
    const ROW_EDIT_URL = 'customer/index/edit/';

    protected $_urlBuilder;
    protected $_customerCollection;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        CustomerCollectionFactory $customerCollectionFactory,
        array $components = [],
        array $data = []
    ) {
        $this->_urlBuilder = $urlBuilder;
        $this->_customerCollection = $customerCollectionFactory->create();
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $customerEmails = array_column($dataSource['data']['items'], 'customer_email');
            $this->_customerCollection->addAttributeToSelect(['email', 'entity_id'])
                ->addAttributeToFilter('email', ['in' => $customerEmails]);

            $customers = [];
            foreach ($this->_customerCollection as $customer) {
                $customers[$customer->getEmail()] = $customer->getId();
            }

            foreach ($dataSource['data']['items'] as &$item) {
                if (isset($customers[$item['customer_email']])) {
                    $userId = $customers[$item['customer_email']];
                    $url = $this->_urlBuilder->getUrl(self::ROW_EDIT_URL, ['id' => $userId]);
                    $item[$this->getData('name')] = "<a href='#' data-action='edit' onclick='window.open(\"{$url}\", \"_blank\")'>{$item[$this->getData('name')]}</a>";
                }
            }
        }
        return $dataSource;
    }
}
