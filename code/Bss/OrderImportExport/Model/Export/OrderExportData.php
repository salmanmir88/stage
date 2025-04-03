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
namespace Bss\OrderImportExport\Model\Export;

class OrderExportData
{
    /**
     * @var array
     */
    protected $data = [];

    /**
     * @param bool $index
     * @return array|mixed
     */
    public function getData($index = false)
    {
        if ($index !== false) {
            return isset($this->data[$index]) ? $this->data[$index] : [];
        }

        return $this->data;
    }

    /**
     * @param $data
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * @param $rowData
     * @param bool $index
     */
    public function addRow($rowData, $index = false)
    {
        if ($index === false) {
            $this->data[] = $rowData;
        } else {
            $this->data[$index] = $rowData;
        }
    }

    /**
     * Reset Data
     */
    public function reset()
    {
        $this->data = [];
    }
}
