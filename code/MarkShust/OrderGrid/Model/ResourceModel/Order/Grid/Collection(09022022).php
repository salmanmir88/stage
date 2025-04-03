<?php declare(strict_types=1);

namespace MarkShust\OrderGrid\Model\ResourceModel\Order\Grid;

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
		$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/multisku.log');
		$logger = new \Zend\Log\Logger();
		$logger->addWriter($writer);


		// pr($field);
        if ($field === 'sku' || $field === 'barcode' && !$this->getFlag('product_filter_added')) {
			
            // Add the sales/order_item model to this collection
            /* $this->getSelect()->join(
                [$this->getTable('sales_order_item')],
                "main_table.entity_id = {$this->getTable('sales_order_item')}.order_id",
                []
            ); */

            // Group by the order id, which is initially what this grid is id'd by
            $this->getSelect()->group('main_table.entity_id');
			
			
			// echo $this->getSelect();
			// die;
			
			// $logger->info("Direct Query multi sku 1 -- ". __FILE__ . " :: ".__LINE__); 
			// $logger->info($this->getSelect());
			

            // On the products field, let's add the sku and name as filterable fields
			/* $this->addFieldToFilter([
                "{$this->getTable('sales_order_item')}.sku",
                "{$this->getTable('sales_order_item')}.barcode",
            ], [
                $condition,
                $condition,
            ]); */
			
			
			// pr($condition);die;
			
			if($condition){
				foreach($condition as $key=>$value){
					$myValue = $value;
				}
			}
			
			// echo $myValue;
			
			// $logger->info("myValue -- ". __FILE__ . " :: ".__LINE__); 
			// $logger->info($myValue);
			
			$myCondition = str_ireplace(["%"],[""],$myValue);
			
			$conditionArray = explode(',',trim($myCondition));
			
			// $newConditionArray = array_map('trim', $conditionArray);
			
			if (is_array($conditionArray)) {
				foreach ($conditionArray as $key => $val) {
					$conditionArray[$key] = trim($val);
				}
			}
			
			// pr($conditionArray);
			
			// $logger->info("conditionArray -- ". __FILE__ . " :: ".__LINE__); 
			// $logger->info($conditionArray);
			
			$this->addFieldToFilter([
                "sku",
                "barcode",
            ], [
                ['in' => $conditionArray],
                $condition
            ]);
			
			$logger->info("Direct Query multi sku 2 -- ". __FILE__ . " :: ".__LINE__); 
			$logger->info($this->getSelect());
			
			
			// echo "<br>";
			// echo $this->getSelect();
			// die;

            $this->setFlag('product_filter_added', 1);

            return $this;
        } else {
            return parent::addFieldToFilter($field, $condition);
        }

    }

    /**
     * Perform operations after collection load.
     *
     * @return SearchResult
     */
    protected function _afterLoad(): SearchResult
    {
        $items = $this->getColumnValues('entity_id');

        if (count($items)) {
            $connection = $this->getConnection();

            // Build out item sql to add products to the order data
            $select = $connection->select()
                ->from([
                    'sales_order_item' => $this->getTable('sales_order_item'),
                ], [
                    'order_id',
                    'product_skus'  => new Zend_Db_Expr('GROUP_CONCAT(`sales_order_item`.sku SEPARATOR ",")'),
                    'product_barcode' => new Zend_Db_Expr('GROUP_CONCAT(`sales_order_item`.barcode SEPARATOR ",")'),
                ])
                ->where('order_id IN (?)', $items)
                ->where('parent_item_id IS NULL') // Eliminate configurable products, otherwise two products show
                ->group('order_id');

                 $items = $connection->fetchAll($select);

            // Loop through this sql an add items to related orders
            foreach ($items as $item) {
                $row = $this->getItemById($item['order_id']);
                $productSku = '';
                if($item['product_skus']){
                $productSku = explode('|', $item['product_skus']);
                }
                $html = '';
                if($productSku){
                    foreach ($productSku as $index => $sku) {
                        $html .= $productSku[$index];
                    }
                }
                $row->setData('sku', $html);
            }

            foreach ($items as $item) {
                $row = $this->getItemById($item['order_id']);
                $barcode = '';
                if($item['product_barcode']){
                $barcode = explode('|', $item['product_barcode']);
                }
                $html = '';
                if($barcode){
                    foreach ($barcode as $index => $sku) {
                        $html .= $barcode[$index];
                    }
                }
                $row->setData('barcode', $html);
            }

        }

        return parent::_afterLoad();
    }
}
