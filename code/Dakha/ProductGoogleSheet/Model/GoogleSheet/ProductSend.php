<?php


namespace Dakha\ProductGoogleSheet\Model\GoogleSheet;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\App\ResourceConnection;

class ProductSend
{
    /**
     * @var Api
     */
    private $googleApi;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory
     */
    protected $itemCollection;

    /**
     * @var \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface
     */
    protected $productSalableQty;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_orderCollectionFactory;

    /**
     * ProductSend constructor.
     * @param Api $googleApi
     * @param DateTime $dateTime
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollection
     * @param \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $productSalableQty
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Dakha\ProductGoogleSheet\Model\GoogleSheet\Api $googleApi,
        DateTime $dateTime,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $itemCollection,
        \Magento\InventorySalesApi\Api\GetProductSalableQtyInterface $productSalableQty,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    )
    {
        $this->googleApi = $googleApi;
        $this->dateTime = $dateTime;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->itemCollection = $itemCollection;
        $this->productSalableQty = $productSalableQty;
        $this->logger = $logger;
        $this->_orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * @param $contact
     * @return false|mixed|string
     */
    public function save()
    {
         $first   = $this->releaseTomorrow();
         $second  = $this->releaseToday();
         $arrCombine1 = array_merge($second,$first);

         $third   = $this->releaseThisWeek();
         $arrCombine2 = array_merge($arrCombine1,$third);

         $fourth  = $this->releaseNextWeek();
         $arrCombine3 = array_merge($arrCombine2,$fourth);

         $fifth   = $this->releaseThisMonth();
         $arrCombine4 = array_merge($arrCombine3,$fifth);

         $sixth   = $this->releaseNextMonth();
         $arrCombine5 = array_merge($arrCombine4,$sixth);

         $seventh = $this->releaseTransitKorea();
         $arrCombine6 = array_merge($arrCombine5,$seventh);

         $this->appendGoogleSheet($arrCombine6);
    }
    
   /**
    *  @return array
    */
    public function releaseNextMonth()
    {
            $gmt7time = date("Y-m-d H:i", strtotime('+7 hours'));
            $createdAt = $this->dateTime->formatDate($gmt7time);
            $firstDayNextMonth = date('Y-m-d', strtotime('first day of next month'));
            try {

                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('release_date',['gteq' => $firstDayNextMonth]);

                 $rowsArr = [['RELEASING NEXT MONTH','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {
                    
                   $realseNextMonth = $product->getName();

                   $sku = $product->getSku();

                   //$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate();
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate)); 
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }
               
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];  
               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        
        return [];
    }

   /**
    * @return array
    */
    public function releaseThisMonth()
    {
            $gmt7time = date("Y-m-d H:i", strtotime('+7 hours'));
            $createdAt = $this->dateTime->formatDate($gmt7time);
            try {
                 $firstDayThisMonth = date('Y-m-01');
                 $lastDayThisMonth  = date('Y-m-t');
    
                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('release_date',['gteq' => $firstDayThisMonth]);
                 $collection->addFieldToFilter('release_date',['lteq' => $lastDayThisMonth]);
                 
                 $rowsArr = [['RELEASING this month','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {
                   $realseNextMonth = $product->getName();

                   $sku = $product->getSku();

                   ///$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate();
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate)); 
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];

               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        
        return [];
    }

   /**
    *  @return array
    */
    public function releaseNextWeek()
    {
            $gmt7time = date("Y-m-d H:i", strtotime('+7 hours'));
            $createdAt = $this->dateTime->formatDate($gmt7time);
            try {
                 $firstdateNextWeek = date('Y-m-d', strtotime("sunday 0 week"));
                 $lastdateNextWeek  = date('Y-m-d', strtotime("sunday 1 week"));
                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('release_date',['gteq' => $firstdateNextWeek]);
                 $collection->addFieldToFilter('release_date',['lteq' => $lastdateNextWeek]);
                 
                 $rowsArr = [['RELEASING next week','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {

                   $realseNextMonth = $product->getName();

                   $sku = $product->getSku();

                   //$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate(); 
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate));
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = []; 
                 
               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        
        return [];
    }

   /**
    *  @return array
    */
    public function releaseThisWeek()
    {
            $gmt7time = date("Y-m-d H:i", strtotime('+7 hours'));
            $createdAt = $this->dateTime->formatDate($gmt7time);
            try {
                 $monday = strtotime("last monday");
                 $monday = date('w', $monday)==date('w') ? $monday+7*86400 : $monday;
                 $sunday = strtotime(date("Y-m-d",$monday)." +6 days");
                 $thisWeekSd = date("Y-m-d",$monday);
                 $thisWeekEd = date("Y-m-d",$sunday);

                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('release_date',['gteq' => $thisWeekSd]);
                 $collection->addFieldToFilter('release_date',['lteq' => $thisWeekEd]);

                 $rowsArr = [['RELEASING this week','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {
                   $realseNextMonth = $product->getName();

                   $sku = $product->getSku();

                   //$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate(); 
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate));
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];

               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        
        return [];
    }

   /**
    *  @return array
    */
    public function releaseTomorrow()
    {
            $gmt7time = date("Y-m-d H:i", strtotime('+7 hours'));
            $createdAt = $this->dateTime->formatDate($gmt7time);
            try {
                 $tomorrowDate = date('Y-m-d', strtotime('+1 days'));

                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('release_date',['eq' => $tomorrowDate]);
                 
                 $rowsArr = [['RELEASING tomorrow','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {
                   $realseNextMonth = $product->getName();

                   $sku = $product->getSku();

                   //$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate();
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate)); 
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = []; 
                 
               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        
        return [];
    }

   /**
    * @return array
    */
    public function releaseToday()
    {
            $gmt7time = date("Y-m-d H:i", strtotime('+7 hours'));
            $createdAt = $this->dateTime->formatDate($gmt7time);
            try {
                 $todayDate = date('Y-m-d');

                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('release_date',['eq' => $todayDate]);
                 $rowsArr = [['RELEASING today','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {
                   $realseNextMonth = $product->getName();

                   $sku = $product->getSku();

                   //$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate(); 
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate));
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];
               $rowsArr[] = [];

               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        
        return [];
    }

   /**
    * @return array
    */
    public function releaseTransitKorea()
    {
        $skuArr = [];
        $objectManager =  \Magento\Framework\App\ObjectManager::getInstance();
        $collection = $this->_orderCollectionFactory->create();
        $collection->addFieldToSelect(array('entity_id'))
                   ->addAttributeToFilter('status','transit');
         /* join with payment table */
        $collection->getSelect()
        ->join(
            ["soi" => "sales_order_item"],
            'main_table.entity_id = soi.order_id',
            array('sku')
        );

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()->columns(['sku' => 'soi.sku']);               
        $skuArr = [];
        foreach($collection as $skus){
           $skuArr[] = $skus->getSku();
        }
        $skuArr = array_unique($skuArr);
            try {
                 $collection = $this->_productCollectionFactory->create();
                 $collection->addAttributeToSelect('*');
                 $collection->addCategoriesFilter(['in' => 6]);
                 //$collection->setPageSize(3);
                 $collection->addFieldToFilter('sku',['in' => $skuArr]);

                 $rowsArr = [['From Pre Order to Transit Korea ( Waiting DHL Number )','SKU','SL','Deadline','Release Date','Barcode']];
                 foreach($collection as $product)
                 {
                   $realseNextMonth = $product->getName();

                   //$count = $this->getTotalOrderCount($product->getSku());
                   $sl = $this->getSoldProductCount($product->getSku());
                   $deadline = '';
                   if($product->getDeadline())
                   {
                     $deadline = $product->getDeadline();
                     $deadline = date("jS \of F Y",strtotime($deadline));
                   }
                   $barcode = '';
                   if($product->getBarcode())
                   {
                     $barcode = $product->getBarcode(); 
                   }
                   $releaseDate = '';
                   if($product->getReleaseDate())
                   {
                     $releaseDate = $product->getReleaseDate(); 
                     $releaseDate = date("jS \of F Y",strtotime($releaseDate));
                   }
                   $rowsArr[] = [$realseNextMonth,$product->getSku(),$sl,$deadline,$releaseDate,$barcode];  
                 }

               return $rowsArr;  
            } catch (\Exception $exception) {
                $this->logger->info($exception->getMessage());
                throw  new CouldNotSaveException(__($exception->getMessage()));
            }
        return [];                      
    }

    /**
     * order count
     * @param $sku
     * @return array
     */
    public function getTotalOrderCount($sku)
    {
        $itemCollection = $this->itemCollection->create()->addFieldToFilter('sku',$sku);
        return count($itemCollection);
    }

     /**
     * order count
     * @param $sku
     * @return array
     */
    public function getSoldProductCount($sku,$firstDate=null,$secondDate=null)
    {
        $totalSoldQty = 0;
        try {
         $collection = $this->_orderCollectionFactory->create()
          ->addFieldToSelect('*');

        /* join with payment table */
        $collection->getSelect()
        ->join(
            ["soi" => "sales_order_item"],
            'main_table.entity_id = soi.order_id',
            array('sku')
        )->where('soi.sku = ?',$sku);

        $collection->getSelect()->reset(\Zend_Db_Select::COLUMNS);
        $collection->getSelect()
            ->columns([
                'qty_ordered' => 'FLOOR(SUM(soi.qty_ordered))',
                'qty_canceled' => 'FLOOR(SUM(soi.qty_canceled))',
                'qty_refunded' => 'FLOOR(SUM(soi.qty_refunded))',
                'qty_sold' => 'FLOOR(SUM(soi.qty_ordered)
                                - SUM(soi.qty_canceled) - SUM(soi.qty_refunded))'
            ]);
        
        foreach($collection as $order)
        {
            $soldQty = $order->getQtySold();
            $totalSoldQty = $soldQty+$totalSoldQty;
        }
        
        return $totalSoldQty;
        
        } catch (\Exception $exception) {
            $this->logger->info($exception->getMessage());
        }
        return $totalSoldQty;

    }

    private function appendGoogleSheet($rowsArr)
    {   
        return $this->googleApi->append($rowsArr);
    }
}
