<?php
/**
 * Copyright © Geo IP Currency Swticher All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Developerswing\GeoIPCurrencySwticher\Model\Config\Source;

class Currency implements \Magento\Framework\Option\ArrayInterface
{

    public function toOptionArray()
    {
        return [['value' => '', 'label' => __('')]];
    }

    public function toArray()
    {
        return ['' => __('')];
    }
}

