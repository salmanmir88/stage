<?php
namespace MarkShust\OrderGrid\Ui\Component\Listing\Column;
 
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Framework\UrlInterface;
 
class Category extends Column
{
    /** Url path */
    
    const ROW_EDIT_URL = 'customer/index/edit/';
    
    /** 
     * @var UrlInterface 
     */

    protected $_urlBuilder;
    
    /** 
     * @var StoreManagerInterface;  
     */
    protected $_storeManager;
    
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    
    /**
     * @var SearchCriteriaBuilder
     */
    protected $_searchCriteria;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $_customer;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $resourceConnection;
    
    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private $productRepository; 
    
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $categoryFactory;

    /**
     * @var string
     */
    private $_editUrl;
   
    public function __construct(
           ContextInterface $context, 
           UiComponentFactory $uiComponentFactory, 
           StoreManagerInterface $storeManager,
           UrlInterface $urlBuilder,
           OrderRepositoryInterface $orderRepository, 
           SearchCriteriaBuilder $criteria, 
           \Magento\Customer\Model\CustomerFactory $customer,
           \Magento\Framework\App\ResourceConnection $resourceConnection,
           \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
           \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryFactory,
           array $components = [], 
           array $data = [],
           $editUrl = self::ROW_EDIT_URL
    )
    {
        $this->_storeManager = $storeManager;
        $this->_urlBuilder = $urlBuilder;
        $this->_editUrl = $editUrl;
        $this->_orderRepository = $orderRepository;
        $this->_searchCriteria  = $criteria;
        $this->_customer = $customer;
        $this->resourceConnection = $resourceConnection;
        $this->productRepository = $productRepository;
        $this->categoryFactory = $categoryFactory;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }
 
    public function prepareDataSource(array $dataSource)
    {

        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                    if ($fieldName=='category_id') {
                      $orderId = $item['entity_id'];
                      $connection = $this->resourceConnection->getConnection();
                      $roleResult = $connection->fetchAll(
                            'SELECT * FROM ' . $connection->getTableName('sales_order_item') . ' ' .
                            'WHERE order_id = :order_id',
                            ['order_id' => $orderId]
                        );
                      $catArr = [];
                      foreach($roleResult as $result)
                      {
                         try {
                             $product = $this->productRepository->get($result['sku']);
                             if(count($product->getCategoryIds())>0)
                             {
                                $collection = $this->categoryFactory->create();
                                $collection->addAttributeToSelect('*');
                                $collection->setStoreId(1);
                                $collection->addAttributeToFilter('entity_id',$product->getCategoryIds());

                                foreach($collection as $category)
                                {
                                  $catArr[] = $category->getName();
                                }

                             }
                         } catch (\Exception $e) {
                             continue;
                         }
                      }
                      $catArr = array_unique($catArr);
                      $string_version = implode(',', $catArr);

                      $item[$this->getData('name')] = $string_version;
                    }
              }
        }
        return $dataSource;
    }

    public function getWebsiteId(){
       return $this->_storeManager->getStore()->getWebsiteId();
    }
}