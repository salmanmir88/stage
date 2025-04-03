<?php

namespace Evince\CourierManager\Ui\Component\Listing\Grid\Column;

class StoreView extends \Magento\Ui\Component\Listing\Columns\Column {

    protected $_countryFactory;

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context, 
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory, 
        \Magento\Directory\Model\CountryFactory $countryFactory, 
        array $components = [], 
        array $data = []
    ) {
        $this->_countryFactory = $countryFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {

                switch ($item['store_ids']) {
                    case 1:
                            $item[$this->getData('name')] = 'English';
                        break;
                    case 2:
                        $item[$this->getData('name')] = 'Arabic';
                        break;
                }
                
            }
        }
        return $dataSource;
    }

}
