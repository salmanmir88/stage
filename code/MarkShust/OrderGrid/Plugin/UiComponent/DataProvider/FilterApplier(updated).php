<?php 

namespace MarkShust\OrderGrid\Plugin\UiComponent\DataProvider;

use Magento\Framework\Api\Filter;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Data\Collection;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterApplierInterface;
use Magento\Sales\Api\Data\OrderInterface;


class FilterApplier
{
    const SALES_ORDER_GRID_NAMESPACE = 'sales_order_grid';
    protected $request;

    public function __construct(
        Http $request
    )
    {
        $this->request = $request;
    }

    public function beforeApply(FilterApplierInterface $subject, Collection $collection, Filter $filter)
    {
        $namespace = $this->request->getParam('sku');
        if ($filter->getField() == 'sku') {
                $modifiedFilterValue = str_replace('%', '', $filter->getValue());
                $modifiedFilterValue = preg_replace('/\s+/', '', $modifiedFilterValue);
                if (strpos($modifiedFilterValue, ",") !== false) {
                    $filter->setValue('%' . $modifiedFilterValue );
                    $filter->setConditionType('like');
                } else {
                    $filter->setValue('%' . $modifiedFilterValue);
                    $filter->setConditionType('like');
                }
				
				// echo $filter->getValue();die;
				/* $modifiedFilterValue = str_replace('%', '', $filter->getValue());
				$modifiedFilterArray = explode(',',trim($modifiedFilterValue));
				if (strpos($modifiedFilterValue, ",") !== false) {
                    $filter->setValue('%' . $modifiedFilterArray );
                    $filter->setConditionType('in');
                } else {
                    $filter->setValue($filter->getValue());
                    $filter->setConditionType('like');
                } */

        }
        if ($filter->getField() == OrderInterface::INCREMENT_ID) {
                $modifiedFilterValue = str_replace('%', '', $filter->getValue());
                $modifiedFilterValue = preg_replace('/\s+/', '', $modifiedFilterValue);
                if (strpos($modifiedFilterValue, ",") !== false) {
                    $filter->setValue($modifiedFilterValue);
                    $filter->setConditionType('in');
                } else {
                    $filter->setValue('%' . $modifiedFilterValue . '%');
                    $filter->setConditionType('like');
                }

        }

        return [$collection, $filter];
    }
}