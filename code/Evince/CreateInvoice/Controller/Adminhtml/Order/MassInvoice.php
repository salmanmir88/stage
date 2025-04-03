<?php

namespace Evince\CreateInvoice\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;

class MassInvoice extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction {

    protected $orderManagement;

    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;

    /**
     * @var \Magento\Framework\DB\Transaction
     */
    protected $_transaction;

    public function __construct(
    Context $context, Filter $filter, CollectionFactory $collectionFactory, OrderManagementInterface $orderManagement, \Magento\Sales\Model\Service\InvoiceService $invoiceService, \Magento\Framework\DB\Transaction $transaction, \Magento\Backend\Model\Auth\Session $authSession
    ) {
        parent::__construct($context, $filter);
        $this->authSession = $authSession;
        $this->collectionFactory = $collectionFactory;
        $this->orderManagement = $orderManagement;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
    }

    protected function massAction(AbstractCollection $collection) {
        $countInvoiceOrder = 0;
        $NonInvoiceOrdernuumbers = '';
        $model = $this->_objectManager->create('Magento\Sales\Model\Order');

//        $username = $this->authSession->getUser()->getUsername();
//        $appendusername = "(" . $username . ")";

        foreach ($collection->getItems() as $order) {
            if (!$order->getEntityId()) {
                continue;
            }
            $loadedOrder = $model->load($order->getEntityId());

            if ($loadedOrder->canInvoice()) {

                // Create invoice for this order
                $invoice = $this->_objectManager->create('Magento\Sales\Model\Service\InvoiceService')->prepareInvoice($loadedOrder);
                //$invoice->getOrder()->setIsInProcess(true);
                $invoice->setShippingAmount($loadedOrder->getShippingAmount());
                $invoice->setBaseShippingAmount($loadedOrder->getBaseShippingAmount());
                $invoice->setTaxAmount($loadedOrder->getTaxAmount());
                $invoice->setBaseTaxAmount($loadedOrder->getBaseTaxAmount());
                $invoice->setSubtotal($loadedOrder->getSubtotal());
                $invoice->setBaseSubtotal($loadedOrder->getBaseSubtotal());
                $invoice->setGrandTotal($loadedOrder->getGrandTotal());
                $invoice->setBaseGrandTotal($loadedOrder->getBaseGrandTotal());

                // Register as invoice item
                $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
                $invoice->register();
                // Save the invoice to the order
                $transaction = $this->_objectManager->create('Magento\Framework\DB\Transaction')
                        ->addObject($invoice)
                        ->addObject($invoice->getOrder());
                $transaction->save();

                //send notification code
//            $loadedOrder->addStatusHistoryComment(
//                __('Notified customer about invoice #%1. '.$appendusername, $invoice->getId())
//            )->setIsCustomerNotified(false)->save();

                if ($loadedOrder->canShip()) {
                    $loadedOrder->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $loadedOrder->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    //$loadedOrder->addStatusToHistory($loadedOrder->getStatus(), 'Order status set to processing using Mass Invoice action '.$appendusername);
                    $loadedOrder->save();
                }

                $countInvoiceOrder++;
            } else {
                if (empty($NonInvoiceOrdernuumbers)) {
                    $NonInvoiceOrdernuumbers = $NonInvoiceOrdernuumbers . $loadedOrder->getIncrementId();
                } else {
                    $NonInvoiceOrdernuumbers = $NonInvoiceOrdernuumbers . ", " . $loadedOrder->getIncrementId();
                }
            }
        }
        $countNonInvoiceOrder = $collection->count() - $countInvoiceOrder;

        if ($countNonInvoiceOrder && $countInvoiceOrder) {
            $this->messageManager->addSuccess(__('%1 order(s) Invoice created successfully.', $countInvoiceOrder));
            $this->messageManager->addError(__('Invoice already created for %1 order(s).', $NonInvoiceOrdernuumbers));
        } elseif ($countNonInvoiceOrder) {
            $this->messageManager->addError(__('Invoice already created for %1 order(s).', $NonInvoiceOrdernuumbers));
        }

        if ($countInvoiceOrder) {
            $this->messageManager->addSuccess(__('%1 order(s) Invoice created successfully.', $countInvoiceOrder));
        }

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath($this->getComponentRefererUrl());
        return $resultRedirect;
    }

}
