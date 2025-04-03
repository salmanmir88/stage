<?php
/**
 * @author      Olegnax
 * @package     Olegnax_DeferJS
 * @copyright   Copyright (c) 2019 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\DeferJS\Block\Adminhtml\System\Config\Form;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Backend system config array field renderer for integration test.
 */
class ActionArray extends AbstractFieldArray
{

    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('condition', ['label' => __('Matched Expression')]);
    }

}
