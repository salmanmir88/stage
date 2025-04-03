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
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * Array to store all options data
     *
     * @var array
     */
    protected $_actions = [];

    /**
     * Sales order
     *
     * @var \Magento\Sales\Helper\Reorder
     */
    protected $orderRepository;

    /**
     * @param \Magento\Backend\Block\Context $context
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Context $context,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;
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
        $order = $this->orderRepository->get($row->getId());
        return $order->getStatusLabel();
    }
}
