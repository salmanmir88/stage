<?php declare(strict_types=1);

namespace Dakha\OrderGridAddAnchor\Model\ResourceModel\Order\Grid;

use Magento\Framework\View\Element\UiComponent\DataProvider\SearchResult;
use Zend_Db_Expr;

/**
 * Class Collection
 * @package MarkShust\OrderGrid\Model\ResourceModel\Order\Grid
 */
class Collection extends SearchResult
{
    /**
     * Initialize the select statement.
     *
     * @return $this
     */
    protected function _initSelect(): self
    {
        $this->addFilterToMap('created_at', 'main_table.created_at')->addFilterToMap('status', 'main_table.status')->addFilterToMap('store_id', 'main_table.store_id');
        parent::_initSelect();

        // Add the sales_order_item model to this collection
        $this->join(
            [$this->getTable('sales_order_item')],
            "main_table.entity_id = {$this->getTable('sales_order_item')}.order_id",
            []
        );

        // Group by the order id, which is initially what this grid is id'd by
        $this->getSelect()->group('main_table.entity_id');

        return $this;
    }

    /**
     * Add field to filter.
     *
     * @param string|array $field
     * @param string|int|array|null $condition
     * @return SearchResult
     */
    public function addFieldToFilter($field, $condition = null): SearchResult
    {


        if ($field === 'product_common_code' && !$this->getFlag('product_filter_added')) {
			
            // Group by the order id, which is initially what this grid is id'd by
            $this->getSelect()->group('main_table.entity_id');
			
			if($condition){
				foreach($condition as $key=>$value){
					$myValue = $value;
				}
			}
			$myCondition = str_ireplace(["%"],[""],$myValue);
			
			$conditionArray = explode(',',trim($myCondition));
			
			if (is_array($conditionArray)) {
				foreach ($conditionArray as $key => $val) {
					$conditionArray[$key] = trim($val);
				}
			}
			
			
			$this->addFieldToFilter([
                "product_common_code",
            ], [
                ['in' => $conditionArray],
                $condition
            ]);
            $this->setFlag('product_filter_added', 1);

            return $this;
        } else {
            return parent::addFieldToFilter($field, $condition);
        }

    }
}
