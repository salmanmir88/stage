<?php

namespace Dakha\OrderGridAddAnchor\Model\Config\Source\Order;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Sales\Model\Order\Config as OrderConfig;

class Status implements OptionSourceInterface
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
        $states = $this->orderConfig->getStatuses();

        foreach ($states as $state => $stateLabel) {
            $options[] = ['value' => $state, 'label' => $stateLabel];
        }
        // Sort the options array by the 'label' key
        usort($options, function ($a, $b) {
            return strcmp($a['label'], $b['label']);
        });

        return $options;
    }
}

