<?php
namespace Developerswing\OrderTracking\Observer;

use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;
class PlaceOrderAfter implements ObserverInterface {
    /**
     * @var \Magento\Catalog\Model\ProductRepository
     */
    protected $productRepository;

    /**
     * PlaceOrder constructor.
     */
    public function __construct(
        \Magento\Catalog\Model\ProductRepository $productRepository,
        ResourceConnection $resourceConnection
    )
    {
        $this->productRepository  = $productRepository;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this|void
     * @throws \Exception
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
            $order = $observer->getEvent()->getOrder();
                foreach ($order->getItems() as $orderItem) {
                    $product = $this->productRepository->getById($orderItem->getProductId());
                    if($product->getReleaseDate() && $product['quantity_and_stock_status']['is_in_stock'] && $product['quantity_and_stock_status']['qty']==0) {
                        $order->setStatus('ship_via_courier');
                        $order->setPreOrder(1);
                        $order->save();
                        $is_pre_order = 1;
                        $orderIncId   = $order->getIncrementId();
                        $connection = $this->resourceConnection->getConnection();
                        $table = $connection->getTableName('sales_order_grid'); 
                        $query = "UPDATE `" . $table . "` SET `pre_order`=".$is_pre_order."  WHERE increment_id = $orderIncId";
                        $connection->query($query);
                        return $this;
                    }
                }
    }
}