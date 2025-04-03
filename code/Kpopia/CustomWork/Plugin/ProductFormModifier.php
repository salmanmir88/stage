<?php

namespace Kpopia\CustomWork\Plugin;

use Magento\Ui\Component\Form;
use Magento\Framework\App\RequestInterface;

class ProductFormModifier
{
    /**
     * Modify manufacturer attribute to use UI Select component
     *
     * @param \Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject
     * @param array $meta
     * @return array
     */
    public function afterModifyMeta(\Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Eav $subject, array $meta)
    {
        $attributeCode = 'manufacturer';

        if (isset($meta['product-details']['children'][$attributeCode])) {
            $meta['product-details']['children'][$attributeCode]['arguments']['data']['config'] = array_merge(
                $meta['product-details']['children'][$attributeCode]['arguments']['data']['config'],
                [
                    'component' => 'Magento_Ui/js/form/element/ui-select',
                    'formElement' => 'select',
                    'elementTmpl' => 'ui/grid/filters/elements/ui-select',
                    'filterOptions' => true,
                    'multiple' => false,
                    'disableLabel' => true,
                    'dataScope' => $attributeCode,
                ]
            );
        }

        return $meta;
    }
}
