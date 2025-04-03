<?php

namespace Amasty\Sorting\Api;

/**
 * Interface IndexMethodWrapper
 * @api
 */
interface IndexMethodWrapperInterface
{
    /**
     * @return \Amasty\Sorting\Api\IndexedMethodInterface
     */
    public function getSource();

    /**
     * @return \Magento\Framework\Indexer\ActionInterface
     */
    public function getIndexer();
}
