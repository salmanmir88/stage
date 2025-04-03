<?php

namespace Amasty\Sorting\Plugin\Catalog\Product\ProductList;

use Amasty\Sorting\Helper\Data;
use Amasty\Sorting\Model\Catalog\Toolbar\GetDefaultDirection;
use Magento\Framework\Registry;

class Toolbar
{
    /**
     * @var Data
     */
    private $helper;

    /**
     * @var \Amasty\Sorting\Model\MethodProvider
     */
    private $methodProvider;

    /**
     * @var \Magento\Catalog\Model\Product\ProductList\Toolbar
     */
    private $toolbarModel;

    /**
     * @var \Amasty\Sorting\Model\ResourceModel\Method\Image
     */
    private $imageMethod;

    /**
     * @var \Amasty\Sorting\Model\ResourceModel\Method\Instock
     */
    private $stockMethod;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var GetDefaultDirection
     */
    private $getDefaultDirection;

    public function __construct(
        Data $helper,
        \Amasty\Sorting\Model\MethodProvider $methodProvider,
        \Magento\Catalog\Model\Product\ProductList\Toolbar $toolbarModel,
        \Amasty\Sorting\Model\ResourceModel\Method\Image $imageMethod,
        \Amasty\Sorting\Model\ResourceModel\Method\Instock $stockMethod,
        Registry $registry,
        GetDefaultDirection $getDefaultDirection
    ) {
        $this->helper = $helper;
        $this->methodProvider = $methodProvider;
        $this->toolbarModel = $toolbarModel;
        $this->imageMethod = $imageMethod;
        $this->stockMethod = $stockMethod;
        $this->registry = $registry;
        $this->getDefaultDirection = $getDefaultDirection;
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param string                                             $dir
     *
     * @return string
     */
    public function afterGetCurrentDirection($subject, $dir)
    {
        $defaultDir = $this->getDefaultDirection->execute($subject->getCurrentOrder());
        $subject->setDefaultDirection($defaultDir);

        if (!$this->toolbarModel->getDirection()
            || $this->shouldSetDirection($subject->getCurrentOrder())
        ) {
            $dir = $defaultDir;
        }

        return $dir;
    }

    /**
     * @param string $order
     *
     * @return bool
     */
    private function shouldSetDirection($order)
    {
        return in_array($order, GetDefaultDirection::ALWAYS_DESC)
            || in_array($order, GetDefaultDirection::ALWAYS_ASC);
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar      $subject
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     *
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeSetCollection($subject, $collection)
    {
        if ($collection instanceof \Magento\Catalog\Model\ResourceModel\Product\Collection) {
            // no image sorting will be the first or the second (after stock). LIFO queue
            $this->imageMethod->apply($collection);
            // in stock sorting will be first, as the method always moves it's paremater first. LIFO queue
            $this->stockMethod->apply($collection);
        }

        return [$collection];
    }

    /**
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $subject
     * @param \Magento\Catalog\Block\Product\ProductList\Toolbar $result
     *
     * @return \Magento\Catalog\Block\Product\ProductList\Toolbar
     */
    public function afterSetCollection($subject, $result)
    {
        $this->applyOrdersFromConfig($subject->getCollection());

        return $result;
    }

    /**
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     */
    private function applyOrdersFromConfig($collection)
    {
        if ($this->registry->registry(Data::SEARCH_SORTING)) {
            $defaultSortings = $this->helper->getSearchSorting();
        } else {
            $defaultSortings = $this->helper->getCategorySorting();
        }
        // first sorting must be setting by magento as default sorting
        array_shift($defaultSortings);

        foreach ($defaultSortings as $defaultSorting) {
            $dir = $this->getDefaultDirection->execute($defaultSorting);
            $collection->setOrder($defaultSorting, $dir);
        }
    }
}
