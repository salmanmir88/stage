<?php

namespace Dakha\CustomWork\Plugin\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Magento\Sales\Api\Data\OrderInterface;


class FilterApplier
{
    const SALES_ORDER_GRID_NAMESPACE = 'amasty_report_sales_order_items_listing';

    protected $request;

    public function __construct(
        Http $request
    )
    {
        $this->request = $request;
    }

    public function beforeApply(FilterApplierInterface $subject, Collection $collection, Filter $filter)
    {
        $namespace = $this->request->getParam('namespace');
    
        if ($namespace=='amasty_report_sales_order_items_listing' && $filter->getField() == 'product_sku') {
                $modifiedFilterValue = str_replace('%', '', $filter->getValue());
                $modifiedFilterValue = preg_replace('/\s+/', '', $modifiedFilterValue);

                if (strpos($modifiedFilterValue, ",") !== false) {
                    $modifiedFilterValue = explode(",",$modifiedFilterValue);

                    $filter->setValue($modifiedFilterValue);
                    $filter->setConditionType('in');
                } else {
                    //$filter->setValue('%' . $modifiedFilterValue);
                    //$filter->setConditionType('like');
                }

        }
        
        return [$collection, $filter];
    }
}