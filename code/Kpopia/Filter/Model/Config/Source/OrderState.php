<?php

namespace Kpopia\Filter\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class OrderState implements OptionSourceInterface
{
    /**
     * @var OrderConfig
     */
    protected $orderConfig;

    public function __construct(OrderConfig $orderConfig)
    {
        $this->orderConfig = $orderConfig;
    }

    /**
     * Retrieve list of order states
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];
        $states = $this->orderConfig->getStates();

        foreach ($states as $state => $stateLabel) {
            $options[] = ['value' => $state, 'label' => $stateLabel];
        }

        return $options;
    }
}
