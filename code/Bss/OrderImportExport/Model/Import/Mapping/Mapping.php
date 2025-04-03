<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_OrderImportExport
 * @author     Extension Team
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\OrderImportExport\Model\Import\Mapping;

use Bss\OrderImportExport\Model\Import\Constant;
use Magento\ImportExport\Model\Import;

/**
 * Class Mapping
 *
 * @package Bss\OrderImportExport\Model\Import\Mapping
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Mapping
{
    /**
     * @var OrderSession
     */
    protected $orderSession;

    /**
     * @var ItemSession
     */
    protected $itemSession;

    /**
     * @var AddressSession
     */
    protected $addressSession;

    /**
     * @var TaxSession
     */
    protected $taxSession;

    /**
     * @var PaymentSession
     */
    protected $paymentSession;

    /**
     * @var ShipmentSession
     */
    protected $shipmentSession;

    /**
     * @var InvoiceSession
     */
    protected $invoiceSession;

    /**
     * @var CreditmemoSession
     */
    protected $creditmemoSession;

    /**
     * @var StatusHistorySession
     */
    protected $statusHistorySession;

    /**
     * @var Tax\ItemSession
     */
    protected $taxItemSession;

    /**
     * @var OrderByEntityIdSession
     */
    protected $orderByEntityIdSession;

    /**
     * @var ShipmentByEntityIdSession
     */
    protected $shipmentByEntityIdSession;

    /**
     * @var InvoiceByEntityIdSession
     */
    protected $invoiceByEntityIdSession;

    /**
     * @var CreditmemoByEntityIdSession
     */
    protected $creditmemoByEntityIdSession;

    /**
     * Mapping constructor.
     * @param OrderSession $orderSession
     * @param ItemSession $itemSession
     * @param AddressSession $addressSession
     * @param TaxSession $taxSession
     * @param PaymentSession $paymentSession
     * @param ShipmentSession $shipmentSession
     * @param InvoiceSession $invoiceSession
     * @param CreditmemoSession $creditmemoSession
     * @param StatusHistorySession $statusHistorySession
     * @param Tax\ItemSession $taxItemSession
     * @param OrderByEntityIdSession $orderByEntityIdSession
     * @param ShipmentByEntityIdSession $shipmentByEntityIdSession
     * @param InvoiceByEntityIdSession $invoiceByEntityIdSession
     * @param CreditmemoByEntityIdSession $creditmemoByEntityIdSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        OrderSession $orderSession,
        ItemSession $itemSession,
        AddressSession $addressSession,
        TaxSession $taxSession,
        PaymentSession $paymentSession,
        ShipmentSession $shipmentSession,
        InvoiceSession $invoiceSession,
        CreditmemoSession $creditmemoSession,
        StatusHistorySession $statusHistorySession,
        Tax\ItemSession $taxItemSession,
        OrderByEntityIdSession $orderByEntityIdSession,
        ShipmentByEntityIdSession $shipmentByEntityIdSession,
        InvoiceByEntityIdSession $invoiceByEntityIdSession,
        CreditmemoByEntityIdSession $creditmemoByEntityIdSession
    ) {
        $this->orderSession = $orderSession;
        $this->itemSession = $itemSession;
        $this->addressSession = $addressSession;
        $this->taxSession = $taxSession;
        $this->paymentSession = $paymentSession;
        $this->shipmentSession = $shipmentSession;
        $this->invoiceSession = $invoiceSession;
        $this->creditmemoSession = $creditmemoSession;
        $this->statusHistorySession = $statusHistorySession;
        $this->taxItemSession = $taxItemSession;

        $this->orderByEntityIdSession = $orderByEntityIdSession;
        $this->shipmentByEntityIdSession = $shipmentByEntityIdSession;
        $this->invoiceByEntityIdSession = $invoiceByEntityIdSession;
        $this->creditmemoByEntityIdSession = $creditmemoByEntityIdSession;
    }

    /**
     * @return array
     */
    protected function getEntities()
    {
        return [
            Constant::PREFIX_ORDER_ITEM => $this->itemSession,
            Constant::PREFIX_ORDER_ADDRESS => $this->addressSession,
            Constant::PREFIX_ORDER_TAX => $this->taxSession,
            Constant::PREFIX_ORDER_PAYMENT => $this->paymentSession,
            Constant::PREFIX_SHIPMENT => $this->shipmentSession,
            Constant::PREFIX_INVOICE => $this->invoiceSession,
            Constant::PREFIX_CREDITMEMO => $this->creditmemoSession,
            Constant::PREFIX_ORDER_STATUS_HISTORY => $this->statusHistorySession,
            Constant::PREFIX_ORDER_TAX_ITEM => $this->taxItemSession,
            Constant::MAPPING_ORDER_BY_ENTITY_ID_KEY => $this->orderByEntityIdSession,
            Constant::MAPPING_SHIPMENT_BY_ENTITY_ID_KEY => $this->shipmentByEntityIdSession,
            Constant::MAPPING_INVOICE_BY_ENTITY_ID_KEY => $this->invoiceByEntityIdSession,
            Constant::MAPPING_CREDITMEMO_BY_ENTITY_ID_KEY => $this->creditmemoByEntityIdSession
        ];
    }

    /**
     * @param $rowData
     * @param bool $hasPrefix
     * @param string $behavior
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function prepareMappingData($rowData, $hasPrefix = false, $behavior = Import::BEHAVIOR_APPEND)
    {
        if ($behavior == Import::BEHAVIOR_APPEND) {
            $this->orderSession->prepareMappingData($rowData, $hasPrefix);
            $this->shipmentSession->prepareMappingData($rowData, $hasPrefix);
            $this->invoiceSession->prepareMappingData($rowData, $hasPrefix);
            $this->creditmemoSession->prepareMappingData($rowData, $hasPrefix);
        } elseif ($behavior == Import::BEHAVIOR_DELETE) {
            $this->orderByEntityIdSession->prepareMappingData($rowData, $hasPrefix);
        } else {
            $this->orderSession->prepareMappingData($rowData, $hasPrefix);
            foreach ($this->getEntities() as $prefix => $entity) {
                $entity->prepareMappingData($rowData, $hasPrefix);
            }
        }
    }

    /**
     * Map all entities id to session
     *
     * @param $behavior
     */
    public function map($behavior = Import::BEHAVIOR_APPEND)
    {
        if ($behavior == Import::BEHAVIOR_APPEND) {
            $this->orderSession->map();
            $this->shipmentSession->map();
            $this->invoiceSession->map();
            $this->creditmemoSession->map();
        } else {
            $this->orderSession->map();
            foreach ($this->getEntities() as $entity) {
                $entity->map();
            }
        }
    }

    /**
     * Clear all mapping session
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function clearMappingSession()
    {
        $this->orderSession->clearMapping();
        foreach ($this->getEntities() as $prefix => $entity) {
            $entity->clearMapping();
        }
    }

    /**
     * @param string $prefix
     * @return array
     */
    public function getMapped($prefix = '')
    {
        if (!$prefix) {
            return $this->orderSession->getMapped();
        }

        $entities = $this->getEntities();
        $entity = isset($entities[$prefix]) ? $entities[$prefix] : false;

        return $entity ? $entity->getMapped() : [];
    }

    /**
     * @param $rowData
     * @param $dbValue
     * @param string $prefix
     */
    public function addMapped($rowData, $dbValue, $prefix = '')
    {
        if (!$prefix) {
            $this->orderSession->addMapped($rowData, $dbValue);
        } else {
            $entities = $this->getEntities();
            $entity = isset($entities[$prefix]) ? $entities[$prefix] : false;
            if ($entity) {
                $entity->addMapped($rowData, $dbValue);
            }
        }
    }
}
