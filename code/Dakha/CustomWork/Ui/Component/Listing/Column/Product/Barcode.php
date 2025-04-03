<?php

namespace Dakha\CustomWork\Ui\Component\Listing\Column\Product;

use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Name
 * @package Amasty\Reports\Ui\Component\Listing\Column\Product
 */
class Barcode extends Column
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

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
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->dataPersistor = $dataPersistor;
        $this->productRepository = $productRepository;
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
                if (isset($item['id_field_name']) && isset($item['product_sku'])) {
                    $product = $this->productRepository->get($item['product_sku']);
                    $item[$this->getData('name')] = $product->getBarcode();
                }
              }
            }

         } catch (\Exception $exception) {
           return $dataSource;
        }
        return $dataSource;
    }
}
