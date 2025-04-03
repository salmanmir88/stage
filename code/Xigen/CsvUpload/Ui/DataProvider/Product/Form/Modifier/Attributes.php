<?php

namespace Xigen\CsvUpload\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

class Attributes extends AbstractModifier
{
    private $arrayManager;

    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $attributes = [
            'special_price_quantity',
            'special_discount_price'
        ];

        foreach ($attributes as $attribute) {
            $path = $this->arrayManager->findPath($attribute, $meta, null, 'children');
            if ($path) {
                $meta = $this->arrayManager->set(
                    "{$path}/arguments/data/config/disabled",
                    $meta,
                    true
                );
            }
        }

        return $meta;
    }
}
