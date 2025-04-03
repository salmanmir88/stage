<?php declare(strict_types=1);

namespace Dakha\OrderGridAddAnchor\Plugin;

use Magento\Framework\Api\Search\DocumentInterface;
use Magento\Ui\Model\Export\MetadataProvider;
use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class FormatOrderItemsExport
{

    protected $_orderRepository;
    protected $_searchCriteria;
    protected $_countryFactory;
    private $productRepository;
    protected $_date;
 
    public function __construct(OrderRepositoryInterface $orderRepository,ProductRepositoryInterface $productRepository,TimezoneInterface $date)
    {
        $this->_orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->_date =  $date;
    }

    /**
     * Place order_items data into sanitized, semicolon-delimited list for order export.
     * @param MetadataProvider $subject
     * @param DocumentInterface $document
     * @param $fields
     * @param $options
     * @return array
     */
    public function beforeGetRowData(
        MetadataProvider $subject,
        DocumentInterface $document,
        $fields,
        $options
    ): array
    {
        
        try {
            if(!$document->getEntityId()){
                return [
                    $document,
                    $fields,
                    $options,
                ];
            }
            $order  = $this->_orderRepository->get($document->getEntityId());
            $cityName = $order->getShippingAddress()->getCity();
            $telephone = $order->getShippingAddress()->getTelephone();
            $country_id = $order->getShippingAddress()->getCountryId();
            $street = $order->getShippingAddress()->getStreet();
            $postcode = $order->getShippingAddress()->getPostcode();
            
            $trackDate = $this->getAlbumReleaseDate($order)??$this->getInvoiceDate($order);
            $week = '';
            if($trackDate){
              $week = floor($trackDate / 7);  
            }
            $document->setData('city', $cityName);
            $document->setData('telephone', $telephone);
            $document->setData('country_id', $country_id);
            $document->setData('street', implode(" ", $street));
            $document->setData('postcode', $postcode);
            $document->setData('days', $trackDate);
            $document->setData('week', $week);

        } catch (Exception $e) {
            
        }

        return [
            $document,
            $fields,
            $options,
        ];
    }

    public function getOrderCreateAt($order)
    {
       return $order->getCreatedAt();
    }

  public function getAlbumReleaseDate($order)
    {
       $releaseDate = ''; 
       try {
           foreach ($order->getAllVisibleItems() as $_item) {
            $product = $this->productRepository->get($_item->getSku());
             if($product->getReleaseDate()){
                $releaseDate = $product->getReleaseDate();
                if($releaseDate >= $this->getOrderInvoiceDate($order))
                {
                  $releaseDate = $product->getReleaseDate();
                  break; 
                }else{
                  $releaseDate = $this->getOrderInvoiceDate($order);   
                }  
             }
           }

           $shipmentDate = $this->getShipmentDate($order);
           $currentDate = $this->_date->date()->format('Y-m-d H:i:s');
           if($shipmentDate){
                $currentDate = $shipmentDate; 
           }

           if($currentDate > $releaseDate && $releaseDate){
             return $this->dateDiffInDays($releaseDate,$currentDate);
           }elseif ($releaseDate >= $currentDate) {
             return $this->dateDiffInDays($currentDate,$releaseDate);  
           }
        } catch (\Exception $e) {
          return;    
       }
       return;
    }

  public function getInvoiceDate($order)
    {
        $orderInvoiceDate = '';
        foreach($order->getInvoiceCollection() as $invoice){
            $orderInvoiceDate = $invoice->getCreatedAt();
            break;
        }

        $shipmentDate = $this->getShipmentDate($order);
        $currentDate = $this->_date->date()->format('Y-m-d H:i:s');
        if($shipmentDate){
            $currentDate = $shipmentDate; 
        }
        if($currentDate > $orderInvoiceDate && $orderInvoiceDate){
             return $this->dateDiffInDays($orderInvoiceDate,$currentDate);
        }
        return;
    }
    
 public function getOrderInvoiceDate($order)
    {
        $orderInvoiceDate = '';
        foreach($order->getInvoiceCollection() as $invoice){
            $orderInvoiceDate = $invoice->getCreatedAt();
            break;
        }
        return $orderInvoiceDate;
    }

  public function getShipmentDate($order)
    {
        $orderShipmentDate = '';
        foreach($order->getShipmentsCollection() as $shipment){
            $orderShipmentDate = $shipment->getCreatedAt();
            break;
        }
        return;
   }
  
      // between two dates.
  public function dateDiffInDays($date1, $date2) 
    {
      $diff = strtotime($date2) - strtotime($date1);
      return abs(round($diff / 86400));
    }  
}

