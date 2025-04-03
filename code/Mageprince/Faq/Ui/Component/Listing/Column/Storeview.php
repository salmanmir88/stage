<?php

/**
 * MagePrince
 * Copyright (C) 2020 Mageprince <info@mageprince.com>
 *
 * @package Mageprince_Faq
 * @copyright Copyright (c) 2020 Mageprince (http://www.mageprince.com/)
 * @license http://opensource.org/licenses/gpl-3.0.html GNU General Public License,version 3 (GPL-3.0)
 * @author MagePrince <info@mageprince.com>
 */

namespace Mageprince\Faq\Ui\Component\Listing\Column;

class Storeview extends \Magento\Ui\Component\Listing\Columns\Column {

    public function __construct(
        \Magento\Framework\View\Element\UiComponent\ContextInterface $context, 
        \Magento\Framework\View\Element\UiComponentFactory $uiComponentFactory, 
        array $components = [], 
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource) {
        if (isset($dataSource['data']['items'])) {

            foreach ($dataSource['data']['items'] as & $item) {

                switch ($item['storeview']) {
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
