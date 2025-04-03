<?php

namespace Amasty\Sorting\Model\Source;

class SearchSortAfter extends SearchSort
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = parent::toOptionArray();
        array_unshift($options, [
            'value' => '',
            'label' => __('--Please Select--')
        ]);

        return $options;
    }
}
