<?php
namespace MarkShust\OrderGrid\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use \Magento\Directory\Model\CountryFactory;

class Country implements ArrayInterface
{
    public function toOptionArray()
    {
        $result = [];
        foreach ($this->getOptions() as $value => $label) {
            $result[] = [
                 'value' => $value,
                 'label' => $label,
             ];
        }

        return $result;
    }

    public function getOptions()
    {
        return [
            'active' => __('Active'),
            'inactive' => __('Inactive'),
            'pending' => __('Pending'),
        ];
    }
}