<?php

namespace Dakha\CustomWork\Ui\Component\Listing\Column\Track;

use \Magento\Sales\Api\OrderRepositoryInterface;
use \Magento\Framework\View\Element\UiComponent\ContextInterface;
use \Magento\Framework\View\Element\UiComponentFactory;
use \Magento\Ui\Component\Listing\Columns\Column;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Magento\Directory\Model\CountryFactory;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use \Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class OrderTracking extends Column
{
  protected $_orderRepository;
  protected $_searchCriteria;
  protected $_countryFactory;
  private $productRepository;
  protected $_date;


  public function __construct(ContextInterface $context, UiComponentFactory $uiComponentFactory, OrderRepositoryInterface $orderRepository, SearchCriteriaBuilder $criteria, CountryFactory $countryFactory, ProductRepositoryInterface $productRepository, TimezoneInterface $date, array $components = [], array $data = [])
  {
    $this->_orderRepository = $orderRepository;
    $this->_searchCriteria  = $criteria;
    $this->_countryFactory = $countryFactory;
    $this->productRepository = $productRepository;
    $this->_date =  $date;
    parent::__construct($context, $uiComponentFactory, $components, $data);
  }

  public function prepareDataSource(array $dataSource)
  {
    if (isset($dataSource['data']['items'])) {
      foreach ($dataSource['data']['items'] as &$item) {
        $order  = $this->_orderRepository->get($item["entity_id"]);
        $countFrom = $this->getCountFrom($order);
        if ($countFrom) {
          $item[$this->getData('name')] = $this->totaldays($countFrom, $order);
        } else {
          $item[$this->getData('name')] = null;
        }
      }
    }
    return $dataSource;
  }

  public function getOrderCreateAt($order)
  {
    return $order->getCreatedAt();
  }

  public function getCountFrom($order)
  {
    $countFrom = '';
    $currentDate = $this->_date->date()->format('Y-m-d H:i:s');
    $latestReleaseDate = null;
    $orderCretedAt = $order->getCreatedAt();
    try {

      foreach ($order->getAllVisibleItems() as $_item) {
        $product = $this->productRepository->get($_item->getSku());

        // Check if the product has a release date
        if ($product->getReleaseDate()) {
          $releaseDate = $product->getReleaseDate();

          // Check if the release date is greater or equal to the current date
          if ($releaseDate >= $orderCretedAt && $releaseDate > $latestReleaseDate) {
            // Assign the release date to $latestReleaseDate if it is the greatest
            $latestReleaseDate = $releaseDate;
          }
        }
      }

      // If there are pre-order items with a future release date, use the latest release date
      if ($latestReleaseDate > $currentDate) {
        $countFrom = null;
      } elseif ($latestReleaseDate <= $currentDate) {
        $countFrom = $latestReleaseDate;
      } else {
        // If no pre-order items, use the invoice date of the order
        $countFrom = $this->getOrderInvoiceDate($order);
      }
    } catch (\Exception $e) {
      // Handle exceptions if necessary
      // $this->_logger->error('Error in getAlbumReleaseDate: ' . $e->getMessage());
    }

    return $countFrom;
  }

  public function getOrderInvoiceDate($order)
  {
    $orderInvoiceDate = null;
    foreach ($order->getInvoiceCollection() as $invoice) {
      $orderInvoiceDate = $invoice->getCreatedAt();
      break;
    }
    return $orderInvoiceDate;
  }
  public function getShipmentDate($order)
  {
    $orderShipmentDate = null;
    foreach ($order->getShipmentsCollection() as $shipment) {
      $orderShipmentDate = $shipment->getCreatedAt();
      break;
    }
    return $orderShipmentDate;
  }

  public function totaldays($countFrom, $order)
  {

    $countTo = $this->getShipmentDate($order);
    if (!$countTo) {
      $countTo = $this->_date->date()->format('Y-m-d H:i:s');
    }
    $totalDaysCount = strtotime($countTo) - strtotime($countFrom);
    return abs(round($totalDaysCount / 86400));
  }
}
