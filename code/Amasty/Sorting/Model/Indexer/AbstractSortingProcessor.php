<?php

namespace Amasty\Sorting\Model\Indexer;

use Magento\Framework\Indexer\AbstractProcessor;

abstract class AbstractSortingProcessor extends AbstractProcessor
{
    public function markIndexerAsInvalid()
    {
        if ($this->isIndexerScheduled()) {
            parent::markIndexerAsInvalid();
        }
    }
}
