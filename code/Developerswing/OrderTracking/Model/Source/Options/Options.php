<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Developerswing\OrderTracking\Model\Source\Options;

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

        return $this->options;
    }
}
