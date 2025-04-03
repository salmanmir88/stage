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
namespace Bss\OrderImportExport\Block\Adminhtml\Export\Filter;

use Magento\ImportExport\Model\Export as ExportModel;

/**
 * Class Form
 *
 * @package Bss\OrderImportExport\Block\Adminhtml\Export\Filter
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Prepare form
     *
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'export_filter_form',
                    'action' => $this->getUrl('*/*/export'),
                    'method' => 'post',
                ],
            ]
        );

        $fieldset = $form->addFieldset('bss_filter_fieldset', ['legend' => __('Entity Attributes')]);

        $fieldset->addField(
            'from',
            'date',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP . '[from]',
                'date_format' => 'Y-mm-dd',
                'time_format' => 'HH:mm:ss',
                'label' => __('From'),
                'title' => __('From'),
                'required' => false,
                'css_class' => 'admin__field-small',
                'class' => 'admin__control-text'
            ]
        );

        $fieldset->addField(
            'to',
            'date',
            [
                'name' => ExportModel::FILTER_ELEMENT_GROUP . '[to]',
                'date_format' => 'Y-mm-dd',
                'time_format' => 'HH:mm:ss',
                'label' => __('To'),
                'title' => __('To'),
                'required' => false,
                'css_class' => 'admin__field-small',
                'class' => 'admin__control-text'
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
