<?php

namespace Meetanshi\GridColumn\Model\Category;

use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class CategoryList implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * Constructor
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->categoryCollectionFactory = $collectionFactory;
    }

    /**
     * Retrieve option array
     *
     * @param bool $addEmpty
     * @return array
     */
    public function toOptionArray($addEmpty = true)
    {
        $collection = $this->categoryCollectionFactory->create();
        $collection->addAttributeToSelect('name');
        $options = [];
        if ($addEmpty) {
            $options[] = ['label' => __('-- Please Select a Category --'), 'value' => ''];
        }
        foreach ($collection as $category) {
            $options[] = ['label' => $category->getName(), 'value' => $category->getId()];
        }
        return $options;
    }
}

