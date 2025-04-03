<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-helpdesk
 * @version   1.2.14
 * @copyright Copyright (C) 2023 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Helpdesk\Model\Mail;

class HelpdeskCustomMessage extends \Magento\Framework\Mail\EmailMessage
{
    /**
     * Add a custom header to the message
     *
     * @param  string              $name
     * @param  string              $value
     * @param  boolean             $append
     * @return HelpdeskCustomMessage           Provides fluent interface

     */
    public function addHeader($name, $value, $append = false)
    {
        $prohibit = array('in-reply-to', 'references');

        if (in_array(strtolower($name), $prohibit)) {

            if (property_exists($this, 'zendMessage')) {
                $this->zendMessage->getHeaders()->addHeaderLine($name, $value);
            }
        }

        return $this;
    }
}
