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
namespace Bss\OrderImportExport\Model\Export\Entity\Tax;

use Bss\OrderImportExport\Model\Export\Entity\AbstractEntity;
use Bss\OrderImportExport\Model\Import\Constant;

class Item extends AbstractEntity
{
    /**
     * Current Entity Id Column
     */
    const COLUMN_ENTITY_ID = 'tax_item_id';

    /**
     * Parent Entity Id Column
     */
    const COLUMN_PARENT_ID = 'tax_id';

    /**
     * @var string
     */
    protected $prefixCode = Constant::PREFIX_ORDER_TAX_ITEM;

    /**
     * Table name for entity
     *
     * @var string
     */
    protected $mainTable = 'sales_order_tax_item';
}
