<?php
namespace AddSearch\Brandsearch\Model\Config\Source;

use Magento\Eav\Model\Config;
use Magento\Framework\Data\OptionSourceInterface;

class BrandOptions implements OptionSourceInterface
{
    protected $eavConfig;

    public function __construct(Config $eavConfig)
    {
        $this->eavConfig = $eavConfig;
    }

    /**
     * Retrieve the list of available brands for the select box
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'manufacturer');
        $options = [];

        if ($attribute && $attribute->getSource()) {
            $options = $attribute->getSource()->getAllOptions(false);
        }

        return $options;
    }
}

