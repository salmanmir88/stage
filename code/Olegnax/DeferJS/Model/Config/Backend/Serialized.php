<?php
/**
 * @author      Olegnax
 * @package     Olegnax_DeferJS
 * @copyright   Copyright (c) 2021 Olegnax (http://olegnax.com/). All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Olegnax\DeferJS\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized as BackendSerialized;

class Serialized extends BackendSerialized
{

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $groups = $this->getData('groups');
        $fieldConfig = $this->getData('field_config');
        $depends = true;
        if (isset($fieldConfig['depends'])) {
            foreach ($fieldConfig['depends']['fields'] as $field) {
                $value = $field['value'];
                $dependPath = $field['dependPath'];
                array_shift($dependPath);
                if (isset($groups[$dependPath[0]]['fields'][$dependPath[1]]['value'])
                    && $groups[$dependPath[0]]['fields'][$dependPath[1]]['value'] != $value
                ) {
                    $depends = false;
                }
            }
        }
        if (is_array($this->getValue())) {
            /** @var array $value */
            $value = $this->getValue();
            if (array_key_exists('__empty', $value)) {
                unset($value['__empty']);
            }
            
            if (empty($value) && !$depends) {
                $value = $this->getOldValue();
            }

            $this->setValue($value);
        }
        parent::beforeSave();
        return $this;
    }

}
