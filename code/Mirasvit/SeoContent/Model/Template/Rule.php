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



namespace Mirasvit\SeoContent\Model\Template;

use Magento\Rule\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Mirasvit\Core\Service\CompatibilityService;
use Mirasvit\Seo\Helper\Serializer;

class Rule extends AbstractModel
{
    const FORM_NAME = 'seo_content_template_form';

    /**
     * @var Rule\Condition\CombineFactory
     */
    private $conditionCombineFactory;
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Rule constructor.
     * @param Rule\Condition\CombineFactory $conditionCombineFactory
     * @param Context $context
     * @param Registry $registry
     * @param FormFactory $formFactory
     * @param TimezoneInterface $localeDate
     * @param Serializer $serializer
     */
    public function __construct(
        Rule\Condition\CombineFactory $conditionCombineFactory,
        Context $context,
        Registry $registry,
        FormFactory $formFactory,
        TimezoneInterface $localeDate,
        Serializer $serializer
    ) {
        $this->conditionCombineFactory  = $conditionCombineFactory;
        $this->serializer               = $serializer;

        parent::__construct($context, $registry, $formFactory, $localeDate);
    }

    /**
     * @return \Magento\Rule\Model\Action\Collection|void
     */
    public function getActionsInstance()
    {
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine|Rule\Condition\Combine
     */
    public function getConditionsInstance()
    {
        return $this->conditionCombineFactory->create();
    }

    /**
     * @return \Magento\Rule\Model\Condition\Combine
     * @throws \Zend_Json_Exception
     */
    public function getConditions()
    {
        if (empty($this->_conditions)) {
            $this->_resetConditions();
        }

        if ($this->hasConditionsSerialized()) {
            $conditions = $this->getConditionsSerialized();
            if (!empty($conditions)) {
                if (CompatibilityService::is21()) {
                    $conditions = $this->serializer->unserialize($conditions);
                } else {
                    $conditions = \Zend_Json::decode($conditions);
                }
                if (is_array($conditions) && !empty($conditions)) {
                    $this->_conditions->loadArray($conditions);
                }
            }
            $this->unsConditionsSerialized();
        }

        return $this->_conditions;
    }
}
