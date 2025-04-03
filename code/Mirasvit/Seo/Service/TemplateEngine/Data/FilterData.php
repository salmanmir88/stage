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



namespace Mirasvit\Seo\Service\TemplateEngine\Data;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;

class FilterData extends AbstractData
{
    private $pageFilters = [];

    /**
     * @var LayerResolver
     */
    private $layerResolver;

    /**
     * FilterData constructor.
     * @param LayerResolver $layerResolver
     */
    public function __construct(
        LayerResolver $layerResolver
    ) {
        $this->layerResolver = $layerResolver;

        parent::__construct();
    }

    /**
     * @return \Magento\Framework\Phrase|string
     */
    public function getTitle()
    {
        return __('Filter Data');
    }

    /**
     * @return array
     */
    public function getVariables()
    {
        return [
            'selected_options',
            'named_selected_options',
        ];
    }

    /**
     * @param string $attribute
     * @param array $additionalData
     * @return false|string|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getValue($attribute, $additionalData = [])
    {
        if (class_exists('Manadev\LayeredNavigation\EngineFilter')) {
            $filters = $this->getManaDevFilters();
        } else {
            $filters = $this->getFilters();
        }

        switch ($attribute) {
            case 'selected_options':
                $value = [];
                foreach ($filters as $options) {
                    $value[] = implode(', ', $options);
                }

                return implode(', ', $value);

            case 'named_selected_options':
                $value = [];
                foreach ($filters as $label => $options) {
                    $value[] = $label . ': ' . implode(', ', $options);
                }

                return implode(', ', $value);
            default:
                return null;
        }
    }

    /**
     * @param array $filters
     * @return array
     */
    public function setPageFilters($filters)
    {
        $this->pageFilters = $filters;

        return $filters;
    }

    public function getPageFilters()
    {
        if (!$this->pageFilters) {
            return $this->layerResolver->get()->getState()->getFilters();
        }

        return $this->pageFilters;
    }

    /**
     * @return array
     */
    private function getFilters()
    {
        $filters = $this->getPageFilters();
        if (!is_array($filters)) {
            $filters = [];
        }

        $result = [];

        foreach ($filters as $filter) {
            if (!$filter->getData('filter')) {
                continue; #to prevent "The filter must be an object. Please set a correct filter" error.
            }

            if (!$filter->getFilter()->getData('attribute_model')) {
                continue;
            }

            $name = $filter->getName();

            if (is_scalar($filter->getData('label')) ||
                $this->isPriceFilter($filter)) {
                $selected = strip_tags($filter->getData('label'));

                $result[$name][] = $selected;
            }
        }

        return $result;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getManaDevFilters()
    {
        $filters = [];
        foreach ($this->layerResolver->get()->getState()->getFilters() as $filter) {
            /** @var mixed $filter */
            if ($filter->isApplied()) {
                $filters[] = $filter;
            }
        }

        $result = [];

        $objectManager  = \Magento\Framework\App\ObjectManager::getInstance();
        $productResource = $objectManager->get('Magento\Catalog\Model\ResourceModel\ProductFactory')->create();

        foreach ($filters as $filter) {
            if (!$filter->getFilter()) {
                continue; #to prevent "The filter must be an object. Please set a correct filter" error.
            }

            if ($filter->getFilter()->getData('param_name') != 'price') {
                $attribute = $productResource->getAttribute($filter->getFilter()->getData('param_name'));

                $name = $filter->getName();
                foreach ($filter->getAppliedOptions() as $key => $value) {
                    if ($attribute->usesSource()) {
                        $optionText = $attribute->getSource()->getOptionText($value);
                    } else {
                        $optionText = $value;
                    }
                    $result[$name][] = $optionText;
                }
            }
        }

        return $result;
    }

    /**
     * @param object $filter
     *
     * @return bool
     */
    private function isPriceFilter($filter)
    {
        if ($filter->getData('filter') instanceof \Magento\CatalogSearch\Model\Layer\Filter\Price ||
            strpos(get_class($filter->getData('filter')), '\Mirasvit\LayeredNavigation\Model\Layer\Filter\Price') !== false) {
            return true;
        }
    }
}
