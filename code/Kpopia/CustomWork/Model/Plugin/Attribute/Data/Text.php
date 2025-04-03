<?php

namespace Kpopia\CustomWork\Model\Plugin\Attribute\Data;

class Text
{
    /**
     * Before plugin for validateValue method
     *
     * @param \Magento\Eav\Model\Attribute\Data\Text $subject
     * @param $value
     * @return array|string
     */
    public function beforeValidateValue(\Magento\Eav\Model\Attribute\Data\Text $subject, $value)
    {
        // Call your custom encodeDiacritics method before validateValue
        $value = $this->encodeDiacritics($value);
        return [$value];
    }

    /**
     * Custom method to encode diacritics
     *
     * @param string $value
     * @return string
     */
    private function encodeDiacritics($value)
    {
        $encoded = $value;
        if (is_string($value)) {
            $encoded = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value);

            // Fallback if iconv fails
            if ($encoded === false) {
                $encoded = $this->removeInvalidCharacters($value);
            }
        }
        return $encoded;
    }

    /**
     * Remove any invalid characters from the string.
     *
     * @param string $value
     * @return string
     */
    private function removeInvalidCharacters($value)
    {
        return mb_convert_encoding($value, 'UTF-8', 'UTF-8');
    }
}
