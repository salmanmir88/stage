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
 * @package   mirasvit/module-seo
 * @version   2.1.11
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Seo\Model\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized as SerializedArraySerialized;

class ArraySerialized extends SerializedArraySerialized
{
    /**
     * @return void
     */
    protected function _afterLoad()
    {
        $value = $this->getValue();
        if (!is_array($value)) {
            $this->setValue(empty($value) ? false : $this->getUnserializedValue($value));
        }
    }

    /**
     * @param string $value
     *
     * @return array
     */
    protected function getUnserializedValue($value)
    {
        if ($value != '[]' && json_decode($value)) { //M2.2 compatibility
            $value = $this->getSerializer()->unserialize($value);
        } elseif ($value != '[]') {
            $value = $this->getSerializer()->unserialize($value);
        }

        return $value;
    }

    /**
     * @return \Magento\Framework\Serialize\Serializer\Json|false
     */
    protected function getSerializer()
    {
        $serializer = \Magento\Framework\App\ObjectManager::getInstance()->get(
            \Mirasvit\Seo\Helper\Serializer::class
        );

        return $serializer;
    }

    /**
     * @return SerializedArraySerialized
     */
    public function beforeSave()
    {
        $value = $this->getValue();

        if (is_array($value)) {
            unset($value['__empty']);
        }

        if ($this->getField() == 'noindex_pages2') {
            $value = $this->normalizeValue($value);
        }

        $this->setValue($value);

        return parent::beforeSave();
    }

    /**
     * @param string $value
     * @return mixed
     */
    private function normalizeValue($value)
    {
        $sortAlphabet = function ($elem1, $elem2) {
            return $elem1['pattern'] > $elem2['pattern'];
        };

        $sortCondition = function ($elem1, $elem2) {
            return strlen($elem2['pattern']) > strlen($elem1['pattern']) &&
                strrpos(str_replace(['/', '*'], [' ', ''], $elem2['pattern']), str_replace(['/', '*'], [' ', ''], $elem1['pattern'])) !== false;
        };

        if (is_array($value)) {
            uasort($value, $sortAlphabet);
            uasort($value, $sortCondition);
        }

        return $value;
    }
}
