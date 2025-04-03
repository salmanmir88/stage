<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Evincemage\OrderedVerified\Model\Source\Options;

use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        if ($this->options === null) {
            $this->options = [['value' => '0' ,'label' => __('No')], ['value' => '1','label' => __('yes')]];
        }

        /*array_walk(
            $this->options,
            function (&$item) {
                $item['__disableTmpl'] = true;
            }
        );*/

        return $this->options;
    }
}
