<?php
/**
 * @copyright Copyright (c) 2017 www.tigren.com
 */

namespace Tigren\Ajaxwishlist\Block;

/**
 * Class Message
 * @package Tigren\Ajaxwishlist\Block
 */
class Message extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Tigren\Ajaxsuite\Helper\Data
     */
    protected $_ajaxsuiteHelper;

    /**
     * Message constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Tigren\Ajaxsuite\Helper\Data $ajaxsuiteHelper,
        array $data
    ) {
        parent::__construct($context, $data);
        $this->_ajaxsuiteHelper = $ajaxsuiteHelper;
    }

    /**
     * @return mixed|string
     */
    public function getMessage()
    {
        $message = $this->_ajaxsuiteHelper->getScopeConfig('ajaxwishlist/general/message');
        if (!$message) {
            $message = __('You have added this product to your wishlist');
        }
        return $message;
    }
}