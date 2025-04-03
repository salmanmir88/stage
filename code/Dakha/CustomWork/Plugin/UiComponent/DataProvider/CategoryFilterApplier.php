<?php

namespace Dakha\CustomWork\Plugin\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;

class CategoryFilterApplier
{
    const SALES_ORDER_GRID_NAMESPACE = 'amasty_report_sales_order_items_listing';

    protected $request;

    public function __construct(
        Http $request
    ) {
        $this->request = $request;
    }

    public function beforeApply(FilterApplierInterface $subject, Collection $collection, Filter $filter)
    {
        $namespace = $this->request->getParam('namespace');

        if ($namespace == self::SALES_ORDER_GRID_NAMESPACE && $filter->getField() == 'category_id') {
            $filterValue = $filter->getValue();

            // Ensure we are handling a string and not an array
            if (is_string($filterValue)) {
                $modifiedFilterValue = str_replace('%', '', $filterValue);
                $modifiedFilterValue = preg_replace('/\s+/', '', $modifiedFilterValue);

                // If it's a single value, use a LIKE condition to match partial values
                $filter->setValue('%' . $modifiedFilterValue . '%');
                $filter->setConditionType('like');

            } elseif (is_array($filterValue)) {
                // If filter value is an array (for multiple selections), apply 'LIKE' condition for each item
                $likeConditions = [];
                foreach ($filterValue as $value) {
                    $value = preg_replace('/\s+/', '', $value); // Remove spaces
                    $likeConditions[] = ['like' => '%' . $value . '%'];
                }

                // Use 'or' condition to match any of the LIKE conditions
                $filter->setValue($likeConditions);
                $filter->setConditionType('or');
            }
        }

        return [$collection, $filter];
    }
}
