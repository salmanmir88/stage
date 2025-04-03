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
namespace Bss\OrderImportExport\Model\ResourceModel;

class GridPool extends \Magento\Sales\Model\ResourceModel\GridPool
{
    /**
     * Refresh grids list
     *
     * @param array $orderIds
     * @return $this
     */
    public function refreshByOrderIds($orderIds)
    {
        foreach ($this->grids as $grid) {
            $grid->refreshMultiple($orderIds, $grid->getOrderIdField());
        }

        return $this;
    }
}
