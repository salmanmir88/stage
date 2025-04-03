<?php

/**
 * Copyright © Developerswing All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Developerswing\OrderTracking\Block\Index;

use Magento\Sales\Api\Data\ShipmentTrackInterface;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory
     */
    protected $trackingCollection;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;
    protected $orderRepository;
    private $logger;
    protected $_productloader;
    protected $_storeManager;
    protected $reader;
    protected $soapClientFactory;
    protected $scopeConfig;
    protected $orderFactory;
    protected $orderstatusdateFactory;
    protected $orderConfig;
    protected $orderStatusResource;
    protected $resourceConnection;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context  $context
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\ResourceModel\Order\Shipment\Track\CollectionFactory $trackingCollection,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Catalog\Api\ProductRepositoryInterface $productrepository,
        \Magento\Store\Model\StoreManagerInterface $storemanager,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\Webapi\Soap\ClientFactory $soapClientFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Sales\Api\Data\OrderInterfaceFactory $orderFactory,
        \Developerswing\OrderTracking\Model\OrderstatusdateFactory $orderstatusdateFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Sales\Model\ResourceModel\Order\Status $orderStatusResource,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Psr\Log\LoggerInterface $logger,
        array $data = []
    ) {
        $this->trackingCollection     = $trackingCollection->create();
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->logger                 = $logger;
        $this->orderRepository        = $orderRepository;
        $this->productrepository      = $productrepository;
        $this->_storeManager          = $storemanager;
        $this->reader                 = $reader;
        $this->soapClientFactory      = $soapClientFactory;
        $this->scopeConfig            = $scopeConfig;
        $this->orderFactory           = $orderFactory;
        $this->orderstatusdateFactory = $orderstatusdateFactory;
        $this->orderConfig = $orderConfig;
        $this->orderStatusResource = $orderStatusResource;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }
    public function getMainOrderStatus($statusCode)
    {
        $orderStatusResource = $this->orderStatusResource;
        $connection = $orderStatusResource->getConnection();
        $select = $connection->select()
            ->from($orderStatusResource->getTable('sales_order_status_state'), ['state'])
            ->where('status = ?', $statusCode);

        $state = $connection->fetchOne($select);
        return $state;
    }
    public function getOnlyPreorderItem($orderId)
    {
        // Initialize as not only preorder
        $onlyPreorder = false;
        $preorderCount = 0;
        $nonPreorderCount = 0;
        // Fetch order and items
        $order = $this->orderRepository->get($orderId);
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($this->getPreOrderCheck($item)) {
                $preorderCount += 1;
            } else {
                $nonPreorderCount += 1;
            }
        }
        // Check conditions for only preorder
        if (($nonPreorderCount === 0) && ($preorderCount > 0)) {
            $onlyPreorder = true;
        }

        return $onlyPreorder;
    }
    public function getHadOnlyPreorderItems($orderId)
    {
        // Initialize as not only preorder
        $onlyPreorder = false;
        $preorderCount = 0;
        $nonPreorderCount = 0;
        // Fetch order and items
        $order = $this->orderRepository->get($orderId);
        $orderDate = $order->getCreatedAt();
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($this->getHadPreOrderCheck($item, $orderDate)) {
                $preorderCount += 1;
            } else {
                $nonPreorderCount += 1;
            }
        }
        // Check conditions for only preorder
        if (($nonPreorderCount === 0) && ($preorderCount > 0)) {
            $onlyPreorder = true;
        }

        return $onlyPreorder;
    }

    public function getNoPreorderItem($orderId)
    {
        // Initialize as no preorder
        $noPreorder = false; // Default to true, assuming no preorder unless proven otherwise

        $preorderCount = 0;
        $nonPreorderCount = 0;
        // Fetch order and items
        $order = $this->orderRepository->get($orderId);
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($this->getPreOrderCheck($item)) {
                $preorderCount += 1;
            } else {
                $nonPreorderCount += 1;
            }
        }
        // Check conditions for only preorder
        if (($preorderCount === 0) && ($nonPreorderCount > 0)) {
            $noPreorder = true;
        }
        return $noPreorder;
    }

    public function getSomePreorderItem($orderId)
    {
        // Initialize as not some preorder
        $somePreorder = false;
        $preorderCount = 0;
        $nonPreorderCount = 0;
        // Fetch order and items
        $order = $this->orderRepository->get($orderId);
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($this->getPreOrderCheck($item)) {
                $preorderCount += 1;
            } else {
                $nonPreorderCount += 1;
            }
        }
        // Check conditions for only preorder
        if (($preorderCount > 0) && ($nonPreorderCount > 0)) {
            $somePreorder = true;
        }
        return $somePreorder;
    }
    public function getHadSomePreorderItems($orderId)
    {
        // Initialize as not some preorder
        $somePreorder = false;
        $preorderCount = 0;
        $nonPreorderCount = 0;

        // Fetch order and items
        $order = $this->orderRepository->get($orderId);
        $orderDate = $order->getCreatedAt();
        $items = $order->getAllItems();
        foreach ($items as $item) {
            if ($this->getHadPreOrderCheck($item, $orderDate)) {
                $preorderCount += 1;
            } else {
                $nonPreorderCount += 1;
            }
        }
        // Check conditions for only preorder
        if (($preorderCount > 0) && ($nonPreorderCount > 0)) {
            $somePreorder = true;
        }
        return $somePreorder;
    }



    public function getEligibleForStoreCredit($orderId)
    {
        // Initialize as not eligible
        $eligibleForStoreCredit = false;

        // Fetch data
        $noPreorder = $this->getNoPreorderItem($orderId);
        $invoiceDate = $this->getOrderInvoiceDate($orderId);

        // Ensure invoice date is valid
        if (!$invoiceDate) {
            return $eligibleForStoreCredit; // Early return if no invoice date is found
        }

        // Calculate age of the invoice in days
        $invoiceDateTimestamp = strtotime($invoiceDate); // Assuming $invoiceDate is a valid date string
        $todayTimestamp = strtotime(date('Y-m-d'));
        $ageOfInvoiceInDays = ($todayTimestamp - $invoiceDateTimestamp) / (60 * 60 * 24);

        // Check eligibility conditions
        if (!$noPreorder && $ageOfInvoiceInDays > 21) {
            $eligibleForStoreCredit = true;
        }

        return $eligibleForStoreCredit;
    }

    public function getPreOrderCheck($item)
    {
        $preOrderCheck = false;
        $product = $this->getProductById($item->getProductId());
        if ($product && $product->getReleaseDate()) {
            $releaseDate = strtotime(date('Y-m-d', strtotime($product->getReleaseDate())));
            $todayDate   = strtotime(date('Y-m-d'));

            if ($product->getReleaseDate() && $releaseDate > $todayDate && $releaseDate != $todayDate) {
                $preOrderCheck = true;
            }
        }
        return $preOrderCheck;
    }
    public function getHadPreOrderCheck($item, $orderDate)
    {
        $preOrderCheck = false;

        $product = $this->getProductById($item->getProductId());
        if ($product && $product->getReleaseDate()) {
            $releaseDate = strtotime(date('Y-m-d', strtotime($product->getReleaseDate())));
            $orderDate   = strtotime($orderDate);
            $todayDate   = strtotime(date('Y-m-d'));

            if ($product->getReleaseDate() && $releaseDate > $orderDate && $releaseDate != $orderDate) {
                $preOrderCheck = true;
            }
        }
        return $preOrderCheck;
    }
    public function getOrderStatusDates($orderId)
    {
        $order = $this->orderstatusdateFactory->create()->getCollection()
            ->addFieldToFilter('order_id', $orderId)
            ->getFirstItem();
        try {
            return $order;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    public function getOrderInvoiceDate($orderId)
    {
        try {
            $order = $this->orderFactory->create()->load($orderId);
            $invoiceCreatedAt = '';
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceCreatedAt = $invoice->getCreatedAt();
            }
            return $invoiceCreatedAt;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }


    public function getCommonMessage()
    {
        $storeId = $this->getStoreId();
        if ($storeId == 1) {
            return '<p>Dear valuable customer please contact us via <span style="color: #25D366">WhatsApp</span> <a href="https://wa.me/966570001626/?text=Hello"  target="_blank">966570001626</a> & Kindly Provide us with your correct mobile number / order number so we can be able to deliver your order</p>';
        } elseif ($storeId == 2) {
            return '<p> برقم هاتفك الجوال مع رقم الطلبية حتى نتمكن من<span style="color: #25D366">الواتس</span><a href="https://wa.me/966570001626/?text=Hello"  target="_blank">,+966570001626</a> ، وتزويدنا برقم هاتفك الجوال مع رقم الطلبية حتى نتمكن من توصيل طلبك</p>';
        }
    }

    public function getTwiceIssueCommonMessage()
    {
        $storeId = $this->getStoreId();
        if ($storeId == 1) {
            return '<p><p style="color:#048ad4">Important notice for Twice Formula Of Love Album:</p> We apologize to you regarding your requests. There is a delay from the factory. The quantity was booked in 1 month. It was confirmed by the factory. We were notified that it will be shipped in the month of 2, but there was a delay until the end of the month 4 according to what the manufacturer said.</p>';
        } elseif ($storeId == 2) {
            return '<p><p style="color:#048ad4">اشعار هام ل البوم Twice Formula Of Love:</p> نعتذر منكم بخصوص طلباتكم هناك تأخير من المصنع وتم حجز الكمية في شهر 1 وتم تأكيده من المصنع وتم اشعارنا انها سوف تنشحن في شهر 2 ولكن حصل تأجيل الى نهاية شهر 4 على حسب كلام المصنع نكرر اعتذارنا ونشكركم على حسن صبركم ونود اعلامكم نسعى جاهدين لتوفير منتجاتكم في أسرع وقت ممكن</p>';
        }
    }

    public function getSMIssueCommonMessage()
    {
        $storeId = $this->getStoreId();
        if ($storeId == 1) {
            return "<p><p style='color:#048ad4'>Important notice for some SM albums that have been requested:</p>
      In the third month of 2022, a small amount of some bands whose albums had expired were released from the factory for pre-booking to be shipped after their release So far, there is no specific date for the album's delivery from the factory, but there is a possibility that it will be in the month of May 5
      We would like to apologize to you for your order, which has an album that has not yet been delivered from the factory
      The Kibobe Shop team is working on it, and God willing, the first time the products reach us, the order will be shipped to you
      We appreciate your waiting and thank you for your patience
      </p>";
        } elseif ($storeId == 2) {
            return '<p><p style="color:#048ad4">تنبيه هام لبعض الألبومات التي تم طلبها لفرق شركة SM:</p>
      في الشهر الثالث من سنة 2022 تم نزول كمية بسيطة لبعض الفرق التي كانت ألبوماتهم منتهية من المصنع للحجز المسبق ليتم شحنها بعد إصدارها
      والى الان لا يوجد تاريخ محدد لتسليم الألبوم من المصنع ولكن هناك احتمالية انها ستكون في شهر 5 مايو
      ونحب ان نعتذر منك بسبب طلبك الذي به ألبوم لم يتم تسليمه الى الان من المصنع 
      فريق كيبوبية شوب يقومون بالعمل عليه وبأذن الله أول ما تصل لنا المنتجات سيتم شحن الطلب لك
      مقدرين انتظاركم ونشكركم على حسن صبركم 
      </p>';
        }
    }

    public function isPreOrder($orderId)
    {
        try {
            $order = $this->orderstatusdateFactory->create()->getCollection()
                ->addFieldToFilter('order_id', $orderId)
                ->getFirstItem();
            return $order;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
    public function getTrackingNumberFrom($order)
    {
        $incrementId = $order->getId();
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('sales_order');
        $query = $connection->select()
            ->from($tableName, ['tracking_link'])
            ->where('entity_id = :increment_id');

        $result = $connection->fetchOne($query, ['increment_id' => $incrementId]);

        return $result ?: null;
    }

    // public function getProcessingDetails($orderId)
    // {
    //     $order = $this->getOrder($orderId);
    //     $items = $order->getAllItems();
    //     $preOrderCheck = $this->getReleaseDateCheck($order);
    //     if ((count($items) === 1) && $preOrderCheck) {
    //     } else {
    //         foreach ($order->getAllItems() as $_item) {
    //             $product = $this->getProductById($_item->getProductId());
    //         }
    //     }
    // }
    public function getTrackingNumber()
    {
        return $this->getRequest()->getParam('order_id');
    }
    public function getOrderNumber()
    {
        return $this->getRequest()->getParam('order_id');
    }
    public function getOrderSearch()
    {
        // Get the 'order_id' parameter from the request
        $orderId = trim($this->getRequest()->getParam('order_id'));

        if ($orderId) {
            // Load the order by increment ID
            $order = $this->orderFactory->create()->loadByIncrementId($orderId);

            // Check if the order exists
            if ($order->getId()) {
                return $this->getOrder($order->getId());
            }
        }

        // Return null if no order is found
        return null;
    }

    public function getOrder($orderId)
    {
        try {
            return $this->orderRepository->get($orderId);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
    public function getOrderDate($orderId)
    {
        $orderDate = $this->getOrder($orderId)->getCreatedAt();
        return $orderDate;
    }
    public function getShipmentDate($orderId)
    {
        $shipmentDate = '';
        try {
            $order = $this->orderRepository->get($orderId);
            foreach ($order->getShipmentsCollection() as $shipment) {
                $shipmentDate = $shipment->getCreatedAt();
            }
            return $shipmentDate;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
            return $shipmentDate;
        }
    }
    public function getProductImageUrl($productid)
    {
        try {
            $store   = $this->_storeManager->getStore();
            $product = $this->productrepository->getById($productid);
            return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $product->getImage();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    public function getMediaUrl()
    {
        $store   = $this->_storeManager->getStore();
        return $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'trackorder/';
    }
    public function getProductById($productId)
    {
        try {
            return $this->productrepository->getById($productId);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
        return false;
    }


    public function getReleaseDateCheck($order)
    {
        try {
            $preOrderCheck = false;
            foreach ($order->getAllItems() as $_item) {
                $product = $this->getProductById($_item->getProductId());
                if ($product && $product->getReleaseDate()) {
                    $releaseDate = strtotime(date('Y-m-d', strtotime($product->getReleaseDate())));
                    $todayDate   = strtotime(date('Y-m-d'));

                    if ($product->getReleaseDate() && $releaseDate > $todayDate && $releaseDate != $todayDate) {
                        $preOrderCheck = true;
                        continue;
                    }
                }
            }
            return $preOrderCheck;
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return false;
    }

    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }
}
