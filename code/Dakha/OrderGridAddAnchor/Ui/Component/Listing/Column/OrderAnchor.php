<?php

namespace Dakha\OrderGridAddAnchor\Ui\Component\Listing\Column;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\UrlInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderAnchor extends BillNameAnchor
{
    const ROW_VIEW_URL = 'sales/order/view/';

    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $url = $this->_urlBuilder->getUrl(self::ROW_VIEW_URL, ['order_id' => $item['entity_id']]);
                $item[$this->getData('name')] = "<a href='#' data-action='view' onclick='window.open(\"{$url}\", \"_blank\")'>{$item[$this->getData('name')]}</a>";
            }
        }
        return $dataSource;
    }
}
