<?php

namespace Amasty\Sorting\Model\Elasticsearch\Adapter\DataMapper;

use Amasty\Sorting\Model\Elasticsearch\Adapter\IndexedDataMapper;

class Wished extends IndexedDataMapper
{
    const FIELD_NAME = 'wished';

    /**
     * @inheritdoc
     */
    public function getIndexerCode()
    {
        return 'amasty_sorting_wished';
    }
}
