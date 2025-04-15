<?php
namespace Kpopia\CurrencySymbol\Block;

use Magento\Framework\View\Element\Template;

class CurrencySymbol extends Template
{
    /**
     * Get the path to the currency symbol image.
     *
     * @return string
     */
    public function getCurrencySymbolImage()
    {
        return $this->getViewFileUrl('Kpopia_CurrencySymbol::images/riyal-symbol.png');
    }
}