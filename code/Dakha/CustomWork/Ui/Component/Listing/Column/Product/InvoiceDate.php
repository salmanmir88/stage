<?php

namespace Dakha\CustomWork\Ui\Component\Listing\Column\Product;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Sales\Model\OrderFactory;

/**
 * Class Name
 * @package Amasty\Reports\Ui\Component\Listing\Column\Product
 */
class InvoiceDate extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    protected $orderFactory;
    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        DataPersistorInterface $dataPersistor,
        OrderFactory $orderFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataPersistor = $dataPersistor;
        $this->productRepository = $productRepository;
        $this->orderFactory = $orderFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        try {

            if (isset($dataSource['data']['items'])) {
                foreach ($dataSource['data']['items'] as &$item) {
                    if (isset($item['order_id'])) {
                        if ($this->getInvoiceDetails($item['order_id'])) {
                            $invoiceDate = $this->getInvoiceDetails($item['order_id']);
                        } else {
                            $invoiceDate = null;
                        }
                        $item[$this->getData('name')] = $invoiceDate;
                    }
                }
            }
        } catch (\Exception $exception) {
            return $dataSource;
        }
        return $dataSource;
    }

    public function getInvoiceDetails($orderId)
    {

        $order = $this->orderFactory->create()->load($orderId);

        foreach ($order->getInvoiceCollection() as $invoice) {

            $invoiceDate = $invoice->getCreatedAt();
        }
        return $invoiceDate;
    }
}
