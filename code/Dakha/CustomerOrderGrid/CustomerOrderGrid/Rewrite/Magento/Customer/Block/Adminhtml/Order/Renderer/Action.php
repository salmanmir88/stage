<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Dakha\CustomerOrderGrid\Rewrite\Magento\Customer\Block\Adminhtml\Order\Renderer;

/**
 * Adminhtml alert queue grid block action item renderer
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Action extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    /**
     * Sales reorder
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $_salesReorder = null;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Helper\Reorder $salesReorder
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Helper\Reorder $salesReorder,
        array $data = []
    ) {
        $this->_salesReorder = $salesReorder;
        parent::__construct($context, $data);
    }

    /**
     * Render actions
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        $html = '<a href="'.$this->getUrl('sales/order/view', ['order_id' => $row->getId()]).'" target="_blank">'.$row->getIncrementId().'</a>';
        return $html;
    }

    /**
     * Get escaped value
     *
     * @param string $value
     * @return string
     */
    protected function _getEscapedValue($value)
    {
        // phpcs:ignore Magento2.Functions.DiscouragedFunction
        return addcslashes($this->escapeHtml($value), '\\\'');
    }

    /**
     * Render options array as a HTML string
     *
     * @param array $actions
     * @return string
     */
    protected function _actionsToHtml(array $actions = [])
    {
        $html = [];
        $attributesObject = new \Magento\Framework\DataObject();

        if (empty($actions)) {
            $actions = $this->_actions;
        }

        foreach ($actions as $action) {
            $attributesObject->setData($action['@']);
            $html[] = '<a ' . $attributesObject->serialize() . ' target="_blank">' . $action['#'] . '</a>';
        }
        return implode('', $html);
    }

    /**
     * Add one action array to all options data storage
     *
     * @param array $actionArray
     * @return void
     */
    public function addToActions($actionArray)
    {
        $this->_actions[] = $actionArray;
    }
}
