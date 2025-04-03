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
         
        if ($field === 'sku' || $field === 'barcode' && !$this->getFlag('product_filter_added')) {
            
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
                "sku",
                "barcode",
            ], [
                ['in' => $conditionArray],
                $condition
            ]);
            $this->setFlag('product_filter_added', 1);
            return $this;
        } elseif($field === 'category_id') {
            
            $this->getSelect()->group('main_table.entity_id');
            
            if($condition){
                foreach($condition as $key=>$value){
                    $myValue = $value;
                }
            }

            $myCondition = str_ireplace(["%"],[""],$myValue);
            
            if (is_string($myCondition)) {
                $conditionArray = explode(',',trim($myCondition));
            }else{
                $conditionArray = $myCondition;    
            }
            
            if (is_array($conditionArray)) {
                foreach ($conditionArray as $key => $val) {
                    $conditionArray[$key] = trim($val);
                }
            }
            
            $skuArrs = [];
            $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
            $prodcollection = $objectManager->get('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory')->create();
            $prodcollection->addAttributeToSelect('*');
            $prodcollection->addCategoriesFilter(['in' => $conditionArray]);
            foreach($prodcollection as $product)
            {
                $skuArrs[] = $product->getSku(); 
            }

            $this->addFieldToFilter([
                "sku",
            ], [
                ['in' => $skuArrs]
            ]);
            $this->setFlag('product_filter_added', 1);
            return $this;
        }else{
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
                    'product_common_code' => new Zend_Db_Expr('GROUP_CONCAT(`sales_order_item`.product_common_code SEPARATOR ",")'),
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

            foreach ($items as $item) {
                $row = $this->getItemById($item['order_id']);
                $barcode = '';
                if($item['product_common_code']){
                $barcode = explode('|', $item['product_common_code']);
                }
                $html = '';
                if($barcode){
                    foreach ($barcode as $index => $sku) {
                        $html .= $barcode[$index];
                    }
                }
                $row->setData('product_common_code', $html);
            }

        }

        return parent::_afterLoad();
    }
}
