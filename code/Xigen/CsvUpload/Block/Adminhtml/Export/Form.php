<?php

namespace Xigen\CsvUpload\Block\Adminhtml\Export;

use Magento\Backend\Block\Widget\Form\Generic;

class Form extends Generic
{
    protected function _prepareForm()
    {
        $form = $this->_formFactory->create(
            [
                'data' => [
                    'id' => 'add_form',
                    'action' => $this->getUrl('xigen_csvupload/export/upload'), // Upload CSV action
                    'method' => 'post',
                    'enctype' => 'multipart/form-data',
                ]
            ]
        );

        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('')]);

        $fieldset->addField(
            'csv_file',
            'file',
            [
                'name' => 'csv_file',
                'label' => __('CSV File'),
                'title' => __('CSV File'),
                'required' => true,
            ]
        );

        $fieldset->addField(
            'submit_button',
            'submit',
            [
                'name' => 'submit',
                'label' => __('Submit'),
                'value' => __('Upload'),
                'title' => __('Submit'),
                'class' => 'action-default scalable save',
            ]
        );

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
