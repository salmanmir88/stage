<?php 
namespace Kpopiashop\AddColumnInGrid\Plugins;
use Magento\Framework\Message\ManagerInterface as MessageManager;
use Magento\Sales\Model\ResourceModel\Order\Grid\Collection as SalesOrderGridCollection;
class AddColumns
{
  private $messageManager;
  private $collection;
  public function __construct(MessageManager $messageManager,
     SalesOrderGridCollection $collection
 ) {
     $this->messageManager = $messageManager;
     $this->collection = $collection;
 }
 public function aroundGetReport(
    \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $subject,
    \Closure $proceed,
    $requestName
) {

    $result = $proceed($requestName);
    if ($requestName == 'sales_order_grid_data_source') {
        if ($result instanceof $this->collection
        ) {

            $select = $this->collection->getSelect();
            $select->join(array(
                    'soa3' => $this->collection->getTable('sales_order_item')),
                    'soa3.order_id=`main_table`.entity_id', array(
                    'barcode' => new \Zend_Db_Expr('group_concat(soa3.barcode SEPARATOR ", ")'),
                    'sku' => new \Zend_Db_Expr('group_concat(soa3.sku SEPARATOR ", ")')
            ))->order('main_table.entity_id');
            
        }
        return $this->collection->addFilterToMap('created_at', 'main_table.created_at')->addFilterToMap('status', 'main_table.status')->addFilterToMap('store_id', 'main_table.store_id');
    }else{

        return $result;
    }

}
}