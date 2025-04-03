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
namespace Bss\OrderImportExport\Model\Import;

/**
 * Class Constant
 *
 * @package Bss\OrderImportExport\Model\Import
 */
class Constant
{
    const PREFIX_ORDER_ITEM = 'item';
    const PREFIX_ORDER_PAYMENT = 'payment';
    const PREFIX_ORDER_PAYMENT_TRANSACTION = 'transaction';
    const PREFIX_ORDER_TAX = 'tax';
    const PREFIX_ORDER_TAX_ITEM = 'tax_item';
    const PREFIX_ORDER_ADDRESS = 'address';
    const PREFIX_ORDER_STATUS_HISTORY = 'status_history';
    const PREFIX_SHIPMENT = 'shipment';
    const PREFIX_SHIPMENT_ITEM = 'shipment_item';
    const PREFIX_SHIPMENT_TRACK = 'shipment_track';
    const PREFIX_SHIPMENT_COMMENT = 'shipment_comment';
    const PREFIX_INVOICE = 'invoice';
    const PREFIX_INVOICE_ITEM = 'invoice_item';
    const PREFIX_INVOICE_COMMENT = 'invoice_comment';
    const PREFIX_CREDITMEMO = 'creditmemo';
    const PREFIX_CREDITMEMO_ITEM = 'creditmemo_item';
    const PREFIX_CREDITMEMO_COMMENT = 'creditmemo_comment';
    const PREFIX_ORDER_DOWNLOAD_LINK = 'download_link';
    const PREFIX_ORDER_DOWNLOAD_LINK_ITEM = 'download_link_item';

    const MAPPING_CUSTOMER = 'customer';
    const MAPPING_ORDER_BY_ENTITY_ID_KEY = 'order_by_entity_id';
    const MAPPING_SHIPMENT_BY_ENTITY_ID_KEY = 'shipment_by_entity_id';
    const MAPPING_INVOICE_BY_ENTITY_ID_KEY = 'invoice_by_entity_id';
    const MAPPING_CREDITMEMO_BY_ENTITY_ID_KEY = 'creditmemo_by_entity_id';

    const BEHAVIOR_UPDATE = "update";
    const INCREMENT_ID_SUFFIX = 'BSS';
}
